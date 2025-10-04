<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        //commands: __DIR__.'/../routes/console.php',
        health: '/laravel-health',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Division Sessions Sync - Every 15 minutes
        $schedule->command('division_sessions:sync')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'             => \App\Http\Middleware\CheckAdmin::class,
            'admin.permissions' => \App\Http\Middleware\CheckAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
