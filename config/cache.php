<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_STORE', 'database'),

    'stores' => [
        'health' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/health'),
            'lock_path' => storage_path('framework/cache/health'),
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => ['database', 'file'],
        ],
    ],

    'serializable_classes' => false,

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')) . '-cache-'),
];
