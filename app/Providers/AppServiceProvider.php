<?php

namespace App\Providers;

use App\Http\ClientAddress;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAdminPermission;
use App\Session\DatabaseSessionHandler;
use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Re-apply the admin route guards to Livewire round trips.
         *
         * Livewire only replays a fixed list of middleware on update requests.
         * That list carries Authenticate, so `auth` holds on every round trip,
         * but neither `admin` nor `admin.permissions:<perm>`: an admin page is
         * checked once, when it loads, and never again. An administrator whose
         * access or permission is removed keeps every action of the page
         * already open, and a snapshot of that page can be replayed later.
         *
         * Livewire matches the route's middleware by class name and keeps its
         * argument, so each page is checked against its own permission, and it
         * aborts the round trip with the redirect these middleware return.
         * Child components (the admin tables) are covered too: they carry the
         * path of the page they were mounted on.
         */
        Livewire::addPersistentMiddleware([
            CheckAdmin::class,
            CheckAdminPermission::class,
        ]);

        /*
         * The database sessions store no IP address: Laravel's handler would
         * write each visitor's own, the proxies being trusted (bootstrap/app.php)
         */
        Session::extend('database', fn (Application $app) => new DatabaseSessionHandler(
            $app['db']->connection($app['config']['session.connection']),
            $app['config']['session.table'],
            $app['config']['session.lifetime'],
            $app,
        ));

        /*
         * Rate limiters of routes/web.php, each with a counter of its own.
         *
         * An unnamed throttle:N,1 counts under one key per account (per IP
         * address for a guest), whatever the route: every unnamed limit
         * shares that counter and compares it to its own maximum, so the
         * robots.txt and sitemap.xml requests spent the pages' budget. A
         * named limiter counts under its name and the key given by ->by():
         * without ->by(), that key is empty and one counter serves
         * everybody (see perVisitor()).
         */
        RateLimiter::for('pages', self::perVisitor(60));
        RateLimiter::for('seo-files', self::perVisitor(100));
    }

    /**
     * A limit per minute for each account, or for each visitor address
     * (ClientAddress) when no one is signed in.
     *
     * The cache keeps a hash of the address keyed with the app key, never
     * the address itself. A request whose address is unknown is not
     * limited: one counter for every guest would let a single client turn
     * them all away.
     */
    private static function perVisitor(int $perMinute): Closure
    {
        return function (Request $request) use ($perMinute): Limit {
            if ($user = $request->user()) {
                return Limit::perMinute($perMinute)->by($user->getAuthIdentifier());
            }

            $address = ClientAddress::of($request);

            return $address === null
                ? Limit::none()
                : Limit::perMinute($perMinute)->by(hash_hmac('sha256', $address, config('app.key')));
        };
    }
}
