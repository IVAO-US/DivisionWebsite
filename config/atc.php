<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ATC Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Air Traffic Control related settings
    |
    */

    /**
     * Approved ATC positions that can be reported
     */
    'approved_positions' => [
        'DEL', // Clearance Delivery
        'GND', // Ground
        'TWR', // Tower
        'DEP', // Departure
        'APP', // Approach
        'CTR', // Center
        'FSS', // Flight Service Station
    ],

    /**
     * ATC ratings mapping
     */
    'ratings' => [
        'AS0' => 'ATC Observer',
        'AS1' => 'ATC Applicant',
        'AS2' => 'ATC Trainee',
        'AS3' => 'Advanced ATC Trainee',
        'ADC' => 'Aerodrome Controller',
        'APC' => 'Approach Controller',
        'ACC' => 'Center Controller',
        'SEC' => 'Senior Controller',
        'SAI' => 'Senior ATC Instructor',
        'CAI' => 'Chief ATC Instructor',
    ],

    /**
     * Training types
     */
    'training_types' => [
        'training' => 'Training',
        'exam' => 'Exam',
        'gca' => 'GCA',
        'checkout' => 'Checkout',
    ],

];