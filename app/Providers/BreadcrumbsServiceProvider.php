<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Traits\BreadcrumbsTrait;

class BreadcrumbsServiceProvider extends ServiceProvider
{
    use BreadcrumbsTrait;

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
        if (request()->route()) {
            View::share('breadcrumbs', $this->getBreadcrumbs());
        }
        
        Blade::directive('breadcrumbs', function () {
            return "<?php echo view('components.breadcrumbs', ['items' => \$breadcrumbs ?? []])->render(); ?>";
        });
    }
}