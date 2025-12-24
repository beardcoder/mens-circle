<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images.
    | Depending on your PHP setup, you may choose one of them.
    | By default, we use GD for compatibility.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'gd'),

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

    /*
    |--------------------------------------------------------------------------
    | Responsive Image Widths
    |--------------------------------------------------------------------------
    |
    | Define the widths to generate for responsive images (srcset).
    | Images will be scaled proportionally to these widths.
    | Set to empty array to disable srcset generation.
    |
    */

    'responsive_widths' => [
        320,   // Mobile small
        480,   // Mobile large
        768,   // Tablet
        1024,  // Desktop small
        1280,  // Desktop medium
        1536,  // Desktop large
        1920,  // Full HD
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Sizes Attribute
    |--------------------------------------------------------------------------
    |
    | The default 'sizes' attribute for responsive images.
    | This tells the browser how much space the image occupies at different viewports.
    |
    */

    'default_sizes' => '(max-width: 640px) 100vw, (max-width: 1024px) 75vw, 50vw',

    /*
    |--------------------------------------------------------------------------
    | Maximum Source Width
    |--------------------------------------------------------------------------
    |
    | Only generate srcset variants smaller than or equal to the original image.
    | If the original is 1200px wide, don't generate 1536px or 1920px versions.
    |
    */

    'respect_original_size' => true,

];
