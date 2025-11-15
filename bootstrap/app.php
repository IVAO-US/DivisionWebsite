<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

use App\Services\SitemapService;
use App\Http\Middleware\SecurityHeaders;

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

        // Aliases
        $middleware->alias([
            'admin'             => \App\Http\Middleware\CheckAdmin::class,
            'admin.permissions' => \App\Http\Middleware\CheckAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
