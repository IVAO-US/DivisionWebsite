<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;

use App\Services\SitemapService;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\BlockUnusedVendorRoutes;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        //commands: __DIR__.'/../routes/console.php',
        health: '/laravel-health',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Regenerate sitemap daily
        $schedule->call(function () {
            app(SitemapService::class)
                ->generate()
                ->writeToFile(public_path('sitemap.xml'));
        })
        ->daily()
        ->description('Regenerate sitemap.xml');

        // Division Sessions Sync - Every 15 minutes
        $schedule->command('division_sessions:sync', ['--forever'])
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware) {

        // Add comprehensive security headers middleware
        $middleware->append(SecurityHeaders::class);

        /*
         * No trustProxies(), on purpose.
         *
         * The site is reached through Cloudflare, then the local proxy of the
         * Plesk host, and PHP receives that proxy's address as REMOTE_ADDR
         * (127.0.0.1 in the sessions table, checked on 2026-09-23). Every
         * per-IP limit of routes/web.php (throttle:60,1, throttle:100,1) is
         * therefore one counter shared by all guests; signed-in members are
         * counted per account. For the same reason the Livewire update
         * endpoint carries no throttle: it would be one counter for every
         * guest's polls and clicks.
         *
         * Trusting the proxies would give each visitor their own counter, but
         * Laravel would then store every visitor's IP address in the sessions
         * table, which the privacy policy does not cover. Should that change,
         * trust the local proxy and Cloudflare only, and only for the client
         * address: $middleware->trustProxies(at: ['127.0.0.1', '::1', ...the
         * ranges of https://www.cloudflare.com/ips/], headers:
         * Request::HEADER_X_FORWARDED_FOR) - never '*', which lets anyone who
         * reaches PHP directly pick their IP with X-Forwarded-For.
         */

        /*
         * Answer 404 on the vendor routes the site does not use.
         *
         * MaryUI registers an upload, a spotlight and a sidebar-toggle route
         * itself, outside the throttle groups of routes/web.php, and its
         * upload stores whatever file a signed-in user sends - here, any IVAO
         * member who logs in through the SSO. Prepended to the `web` group,
         * the check runs before the session starts, so these paths create no
         * session row either (see the middleware to enable one of them).
         */
        $middleware->web(prepend: [
            BlockUnusedVendorRoutes::class,
        ]);

        // Aliases
        $middleware->alias([
            'admin'             => \App\Http\Middleware\CheckAdmin::class,
            'admin.permissions' => \App\Http\Middleware\CheckAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render exceptions as JSON for API routes (Laravel 13 skeleton default)
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
