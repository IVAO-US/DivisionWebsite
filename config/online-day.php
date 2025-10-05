<?php

return [
    /*
    |--------------------------------------------------------------------------
    | US Online Day Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the recurring US Online Day event that appears
    | in calendars and listings without being stored in the database.
    |
    */
    
    'enabled' => true,
    'title' => 'US Online Day',
    'day_of_week' => 4, // 1 (Monday) to 7 (Sunday) - 4 = Thursday
    'time_start' => '18:00:00',
    'time_end' => '06:00:00',
    'type' => 'online_day',
    'illustration' => 'https://assets.us.ivao.aero/uploads/OnlineDay-15MAR2025.png',
    'description' => 'Join us <b>every Thursday between 18:00 UTC and 06:00 UTC</b>.',
];