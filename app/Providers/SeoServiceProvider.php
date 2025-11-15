<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Lang;
use Artesaos\SEOTools\Facades\SEOMeta;

class SeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Lang::has('seo.description.default')) {
            SEOMeta::setDescription(Lang::get('seo.description.default'));
        }

        if (Lang::has('seo.keywords.default')) {
            SEOMeta::addKeyword(Lang::get('seo.keywords.default'));
        }
    }
}
