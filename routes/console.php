<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * Register the application's console commands and scheduled tasks
 */


// Division Sessions Sync - Runs every 15 minutes
Schedule::command('division_sessions:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();