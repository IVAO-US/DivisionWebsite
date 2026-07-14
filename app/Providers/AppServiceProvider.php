<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mary\View\Components\Badge;

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
        // MaryUI >= 2.9.2 (PR robsontenorio/mary#1099): the <x-tab> template renders
        // its internal badge as <x-mary-badge>, but MaryServiceProvider never registers
        // a "mary-badge" alias (the badge is only bound to the configurable prefix, i.e.
        // <x-badge> with the default empty prefix). Since Blade resolves <x-...> tags at
        // compile time — before the badge's @if — any page containing an <x-tab> would
        // throw "Unable to locate a class or view for component [mary-badge]". We register
        // the missing alias here so tabs keep working across the 2.9.x range.
        Blade::component('mary-badge', Badge::class);
    }
}
