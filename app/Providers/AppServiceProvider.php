<?php

namespace App\Providers;

use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAdminPermission;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
         * Rate limiters of routes/web.php, each with a counter of its own.
         *
         * An unnamed throttle:N,1 counts under one key per account (per IP
         * address for a guest), whatever the route: every unnamed limit
         * shares that counter and compares it to its own maximum, so the
         * robots.txt and sitemap.xml requests spent the pages' budget. A
         * named limiter counts under its name and the key given by ->by():
         * without ->by(), that key is empty and one counter serves
         * everybody. Guests all share the proxy's address (bootstrap/app.php).
         */
        RateLimiter::for('pages', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
        RateLimiter::for('seo-files', fn (Request $request) => Limit::perMinute(100)->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }
}
