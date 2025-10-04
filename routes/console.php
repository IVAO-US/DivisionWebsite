<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * Register the application's console commands and scheduled tasks
 * 
 * This file replaces app/Console/Kernel.php in Laravel 11+
 */

// ============================================
// Console Commands
// ============================================

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ============================================
// Scheduled Tasks
// ============================================

// Sync division sessions from remote database every 15 minutes
Schedule::command('division_sessions:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
