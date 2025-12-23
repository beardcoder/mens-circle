<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" and "Vips" to
    | process images. Depending on your PHP setup, you may choose one of
    | them. By default, we use Vips for best performance.
    |
    | Supported: "gd", "imagick", "vips"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'vips'),

    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | Define the default quality for image optimization.
    | Value from 0 (worst) to 100 (best).
    |
    */

    'quality' => [
        'webp' => env('IMAGE_QUALITY_WEBP', 85),
        'avif' => env('IMAGE_QUALITY_AVIF', 85),
        'jpeg' => env('IMAGE_QUALITY_JPEG', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache image format information.
    | Default: 2592000 (30 days)
    |
    */

    'cache_duration' => env('IMAGE_CACHE_DURATION', 2592000),

    /*
    |--------------------------------------------------------------------------
    | Formats to Generate
    |--------------------------------------------------------------------------
    |
    | Which modern image formats should be automatically generated.
    |
    */

    'formats' => [
        'webp' => env('IMAGE_GENERATE_WEBP', true),
        'avif' => env('IMAGE_GENERATE_AVIF', true),
    ],

];
