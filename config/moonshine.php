<?php

declare(strict_types=1);

use App\MoonShine\Pages\Dashboard;
use MoonShine\Laravel\Http\Middleware\Authenticate;
use MoonShine\Laravel\Http\Middleware\SecurityHeadersMiddleware;

return [
    /**
     * URL prefix for MoonShine panel (separate from Filament)
     * Filament runs on /admin, MoonShine will run on /moonshine
     */
    'prefix' => env('MOONSHINE_ROUTE_PREFIX', 'moonshine'),

    /**
     * Middleware stack for MoonShine routes
     */
    'middleware' => [
        'web',
    ],

    /**
     * Middleware for authenticated routes
     */
    'auth_middleware' => [
        SecurityHeadersMiddleware::class,
        Authenticate::class,
    ],

    /**
     * Page title and branding
     */
    'title' => env('MOONSHINE_TITLE', 'Männerkreis Niederbayern - MoonShine'),
    'logo' => env('MOONSHINE_LOGO', '/logo-color.svg'),
    'logo_small' => env('MOONSHINE_LOGO_SMALL', '/logo-color.svg'),

    /**
     * Authentication configuration
     */
    'auth' => [
        'enabled' => true,
        'guard' => 'moonshine',
        'model' => \MoonShine\Laravel\Models\MoonshineUser::class,
        'pipelines' => [],
    ],

    /**
     * Localization
     */
    'locale' => 'de',
    'locales' => ['de', 'en'],

    /**
     * Default disk for file uploads
     */
    'disk' => env('MOONSHINE_DISK', 'public'),

    /**
     * Cache configuration
     */
    'cache' => 'array',

    /**
     * Home page configuration
     */
    'home_page' => Dashboard::class,

    /**
     * Pagination configuration
     */
    'pagination' => [
        'default' => 25,
        'options' => [10, 25, 50, 100],
    ],

    /**
     * Notifications configuration
     */
    'notifications' => [
        'enabled' => true,
        'model' => \MoonShine\Laravel\Models\MoonshineUserNotification::class,
    ],
];
