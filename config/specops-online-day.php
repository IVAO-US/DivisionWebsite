<?php

return [
    /*
    |--------------------------------------------------------------------------
    | US SpecOps Online Day Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the recurring US SpecOps Online Day event that appears
    | in calendars and listings without being stored in the database.
    | This event occurs on the 3rd Wednesday of each month.
    |
    */
    
    'enabled' => true,
    'title' => 'US SpecOps Online Day',
    'day_of_week' => 3, // /!\ 0 = SUNDAY
    'nth_week' => 3, // Every 3rd week of the month
    'time_start' => '18:00:00',
    'time_end' => '22:00:00',
    'type' => 'online_day',
    'illustration' => 'https://assets.us.ivao.aero/uploads/SO4.jpg',
    'description' => 'Join us <b>every 3rd Wednesday of the month between 18:00 UTC and 22:00 UTC for SpecOps!</b>.',
];