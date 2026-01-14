<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\IvaoController;

use App\Services\SitemapService;

/**
 * ROUTING CONVENTIONS FOR AUTOMATIC BREADCRUMBS
 * ---------------------------------------------
 * 
 * 1. All sub-URL roots must be named using the same base:
 *    Example: /sub-url -> name('sub-url')
 * 
 * 2. The index route must be named 'home'
 * 
 * 3. All pages must have named routes
 * 
 * 4. Pages requiring a single argument must place this argument at the end of the URL,
 *    and the argument will not be included in the breadcrumb
 *    Example: Volt::route('/flight/edit/{id}', 'protected.logbooks.edit')->name('flight.edit');
 * 
 * 5. Pages with multiple arguments must use $_GET parameters,
 *    and these arguments will not be included in the breadcrumb
 *    Example: Route::get('/password-reset', function (Request $request) {
 *                 return redirect()->route('users.password-reset', $request->query());
 *             })->name('password.reset');
 */


/* -- */

/* Rate limiting (60 requests/minute) */
Route::middleware(['throttle:60,1'])->group(function () {

    /**
     *  Log in
     *  IVAO: Handling within the auth-button component & with a callback route
     */

    Route::redirect('/login', '/auth/ivao/callback')->name('login');       /* Alias for Middleware::Auth */
    Route::get('/auth/ivao/callback', [IvaoController::class, 'handleCallback'])->name('auth.ivao.callback');


    /**
     *  Log out 
     *  IVAO: Handling within the auth-button component
     */


    /**
     *  Breadcrumbs
     *  Named routes for categories without index page
     */
    Route::redirect('/users',       '/users/settings')->name('users');
    Route::redirect('/division',    '/')->name('division');
    Route::redirect('/members',     '/')->name('members');
    Route::redirect('/atcs',        '/')->name('ATCs');
    Route::redirect('/pilots',      '/')->name('pilots');
    Route::redirect('/training',    '/')->name('training');
        /* Admin breadcrumbs => make sure to redirect to dashboard */
        Route::redirect('/admin',               '/admin/dashboard')->name('Admin');
        Route::redirect('/admin/app',           '/admin/dashboard')->name('Application');
        Route::redirect('/admin/flight-ops',    '/admin/dashboard')->name('Flight Operations');


    /**
     *  Unprotected routes
     */

    /* Home */
    Route::livewire('/', 'pages::home')->name('home');

    /* Division */
    Route::livewire('/division/our-history',    'pages::division.our-history')->name('division.our-history');
    Route::livewire('/division/transfer',       'pages::division.transfer')->name('division.transfer');

    /* Community */
    Route::livewire('/members/support',         'pages::members.support')->name('members.support');

    /* ATCs */
    Route::livewire('/atcs/become-atc',         'pages::atcs.become-atc')->name('atcs.become-atc');

    /* Pilots */
    Route::livewire('/pilots/virtual-airlines', 'pages::pilots.virtual-airlines')->name('pilots.virtual-airlines');

    /* Training */
    Route::livewire('/training/request',    'pages::training.request')->name('training.request');
    Route::livewire('/training/exams',      'pages::training.exams')->name('training.exams');
    Route::livewire('/training/gca',        'pages::training.gca')->name('training.gca');

    /* Privacy Policy + Terms of Service */
    Route::livewire('/tos',     'pages::tos')           ->name('tos');
    Route::livewire('/privacy', 'pages::privacy-policy')->name('privacy');


    /**
     *  Protected routes
     *  Logged-in auth user status required
     */

    Route::middleware('auth')->group(function () {

        /* User profile */
        Route::livewire('/users/settings', 'pages::protected.users.settings')->name('users.settings');


        /**
         *  Protected routes
         *  Admin status required
         */
        Route::middleware('admin')->group(function () {

            /* Dashboard: no specific permission required */
            Route::livewire('/admin/dashboard', 'pages::protected.admin.index')->name('admin.index');

            /* Manage admin */
            Route::middleware('admin.permissions:admins_edit_permissions')->group(function () {
                Route::livewire('/admin/manage', 'pages::protected.admin.manage')->name('admin.manage');
            });

            /* Application: Headline / GDPR */
            Route::middleware('admin.permissions:app_headline')->group(function () {
                Route::livewire('/admin/app/headline', 'pages::protected.admin.app.headline')->name('admin.app.headline');
            });
            Route::middleware('admin.permissions:app_gdpr')->group(function () {
                Route::livewire('/admin/app/gdpr', 'pages::protected.admin.app.gdpr')->name('admin.app.gdpr');
            });

            /* Flight Ops : Tours + VAs */
            Route::middleware('admin.permissions:fltops_tours')->group(function () {
                Route::livewire('/admin/flight-ops/tours', 'pages::protected.admin.flight-ops.tours')->name('admin.flight-ops.tours');
            });
            Route::middleware('admin.permissions:fltops_va')->group(function () {
                Route::livewire('/admin/flight-ops/virtual-airlines', 'pages::protected.admin.flight-ops.virtual-airlines')->name('admin.flight-ops.virtual-airlines');
            });

        });
    });
});

/**
 * Sitemap + Robots.txt
 * Rate limiting (100 requests/minute)
 * */

Route::middleware(['throttle:100,1'])->group(function () {
    
    Route::get('/robots.txt', function () {
        if (config('seotools.robots.block_in_non_production') && !app()->environment('production')) {
            return response("User-agent: *\nDisallow: /")
                ->header('Content-Type', 'text/plain');
        }

        $disallowedPaths = config('seotools.robots.disallowed_paths', []);
        $sitemapUrl = config('app.url') . '/sitemap.xml';
        
        $content = "User-agent: *\nAllow: /\n\n";
        
        // Add disallowed paths
        foreach ($disallowedPaths as $path) {
            $content .= "Disallow: {$path}\n";
        }
        
        $content .= "\nSitemap: {$sitemapUrl}";
        
        return response($content)->header('Content-Type', 'text/plain');
    })->name('robots');

    Route::get('/sitemap.xml', function (SitemapService $sitemapService) {
        return $sitemapService->generate()->toResponse(request());
    })->name('sitemap');
}); 