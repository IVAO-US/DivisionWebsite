<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEOTools Configuration
    |--------------------------------------------------------------------------
    */
    'meta' => [
        'defaults' => [
            'title' => env('APP_NAME', 'App Name'),
            'titleBefore' => false,
            'description' => 'The official IVAO United States Division website.',
            'separator' => ' - ',
            'keywords' => array('ivao', 'us', 'usa', 'virtual', 'aviation', 'american', 'atc'),
            'canonical' => null,
            'robots' => 'index,follow',
        ],
        'webmaster' => [
            'google' => env('SEO_GOOGLE_VERIFICATION', null),
            'bing' => env('SEO_BING_VERIFICATION', null),
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
            'norton' => null,
        ],
        'add_notranslate_class' => false,
    ],
    
    'opengraph' => [
        'defaults' => [
            'title' => env('APP_NAME', 'Laravel Starter'),
            'description' => 'The official IVAO United States Division website.',
            'url' => env('APP_URL'),
            'type' => 'website',
            'site_name' => env('APP_NAME', 'Laravel Starter'),
            'images' => [
                env('APP_URL') . 'assets/seo/snapshot.jpg',
            ],
        ],
    ],
    
    'twitter' => [
        'defaults' => [
            'card' => 'summary_large_image',
            //'site' => env('SEO_TWITTER_SITE', '@yourhandle'),
            //'creator' => env('SEO_TWITTER_CREATOR', '@yourhandle'),
            'title' => env('APP_NAME', 'Laravel Starter'),
            'description' => 'The official IVAO United States Division website.',
            'image' => env('APP_URL') . 'assets/seo/snapshot.jpg',
        ],
    ],
    
    'json-ld' => [
        'defaults' => [
            'title' => env('APP_NAME', 'Laravel Starter'),
            'description' => 'The official IVAO United States Division website.',
            'url' => env('APP_URL'),
            'type' => 'WebPage',
            'images' => [
                env('APP_URL') . 'assets/seo/snapshot.jpg',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        /*
        | Static pages to include in sitemap
        | Format: ['route' => 'route.name', 'priority' => float, 'frequency' => 'monthly']
        */
        'static_pages' => [
            [
                'route' => 'tos',
                'priority' => 0.5,
                'frequency' => 'monthly',
            ],
            [
                'route' => 'privacy',
                'priority' => 0.5,
                'frequency' => 'monthly',
            ],
        ],

        /*
        | Homepage priority and frequency
        */
        'homepage' => [
            'priority' => 1.0,
            'frequency' => 'daily',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt Configuration
    |--------------------------------------------------------------------------
    */
    'robots' => [
        /*
        | Paths to disallow in robots.txt
        */
        'disallowed_paths' => [],

        /*
        | Block all robots in non-production environments
        */
        'block_in_non_production' => true,
    ],
];