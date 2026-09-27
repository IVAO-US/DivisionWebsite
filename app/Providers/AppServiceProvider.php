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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Livewire;
use Mary\Traits\Toast;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
         * Livewire's update endpoint, with a limit of its own.
         *
         * Declared here, before Livewire boots, so that Livewire registers no
         * default route (HandleRequests::boot()). Livewire adds the `web`
         * group, its header guard and the livewire.update name; the
         * livewire-update limiter is defined in boot() and resolved on each
         * request.
         */
        Livewire::setUpdateRoute(fn ($handle, $path) => Route::post($path, $handle)->middleware('throttle:livewire-update'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Refuse browser calls of MaryUI's toast methods.
         *
         * Mary\Traits\Toast adds toast(), success(), warning(), error() and
         * info() as public methods, and the browser can call any public
         * method of a component. toast() compiles its icon with
         * Blade::render(): a forged icon would be a Blade template run on the
         * server, reachable without an account on any public page using the
         * trait (the homepage). The `call` event fires before the method runs
         * (HandleComponents::callMethods), so Blade::render() is never
         * reached; a component's own $this->success(...) is a direct PHP call
         * and does not pass here. MethodNotFoundException is the refusal of an
         * unknown method: an unlogged 419 outside debug.
         */
        Livewire::listen('call', function (Component $component, string $method) {
            if (in_array($method, ['toast', 'success', 'warning', 'error', 'info'], true)
                && in_array(Toast::class, class_uses_recursive($component), true)) {
                throw new MethodNotFoundException($method);
            }
        });

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

        /*
         * Livewire round trips: the four carousels of the homepage poll every
         * four seconds, up to 60 requests a minute for each open tab, and
         * Livewire shows a 429 in a modal. The budget leaves room for ten tabs
         * while holding a single client to ten requests a second.
         */
        RateLimiter::for('livewire-update', self::perVisitor(600));

        /*
         * No Livewire lockout shared by a whole address.
         *
         * Livewire counts invalid checksums per IP address and, after 10 in
         * 10 minutes, answers 429 to every Livewire request from it, keyed by
         * the raw address in the cache. The event fires before the failure is
         * counted: answering it with the 419 Livewire gives in production
         * counts and stores nothing, and livewire-update bounds the sender.
         * In debug, Livewire keeps its own detailed error.
         */
        Livewire::listen('checksum.fail', function () {
            if (! config('app.debug')) {
                abort(419);
            }
        });

        /*
         * Send no action's return value to the browser.
         *
         * Livewire returns the value of every method a request calls
         * (effects.returns), and any public method can be called: with(), a
         * legacy get*Property() or a public helper would hand out the models
         * they return, every attribute included (the admins' e-mail
         * addresses). Nothing on the site reads these values: before using
         * $wire.method().then() or a #[Json] method, exempt it here.
         */
        Livewire::listen('response', fn () => function (array $payload): array {
            foreach ($payload['components'] as $index => $component) {
                if (isset($component['effects']['returns'])) {
                    $payload['components'][$index]['effects']['returns'] = array_map(fn () => null, $component['effects']['returns']);
                }
            }

            return $payload;
        });
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
