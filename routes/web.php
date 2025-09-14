<?php
use Livewire\Volt\Volt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\IvaoController;

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
Route::redirect('/users', '/users/settings')->name('users');
Route::redirect('/admin', '/admin/dashboard')->name('admin');


/**
 *  Unprotected routes
 */

/* Home */
Volt::route('/', 'home')->name('home');

/* Privacy Policy + Terms of Service */
Volt::route('/tos',     'tos')           ->name('tos');
Volt::route('/privacy', 'privacy-policy')->name('privacy');


/**
 *  Protected routes
 *  Logged-in auth user status required
 */

Route::middleware('auth')->group(function () {

    /* User profile */
    Volt::route('/users/settings', 'protected.users.settings')->name('users.settings');


    /**
     *  Protected routes
     *  Admin status required
     */
    Route::middleware('admin')->group(function () {

        /* Dashboard: no specific permission required */ 
        Volt::route('/admin/dashboard', 'protected.admin.index')->name('admin.index');

        /* Manage admin */
        Route::middleware('admin.permissions:admins_edit_permissions')->group(function () {
            Volt::route('/admin/manage', 'protected.admin.manage')->name('admin.manage');
        });

        /* Review gdpr */
        Route::middleware('admin.permissions:app_gdpr')->group(function () {
            Volt::route('/admin/app/gdpr', 'protected.admin.app.gdpr')->name('admin.app.gdpr');
        });
    });
});