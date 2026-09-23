<?php

namespace App\Providers;

use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAdminPermission;
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
    }
}
