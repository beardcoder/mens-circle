<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configure analytics tracking for the application.
    |
    */

    'umami' => [
        'enabled' => env('UMAMI_ENABLED', false),
        'website_id' => env('UMAMI_WEBSITE_ID'),
        'script_url' => env('UMAMI_SCRIPT_URL', 'https://cloud.umami.is/script.js'),
        'tracking_pixel_url' => env('UMAMI_TRACKING_PIXEL_URL'),
    ],
];
