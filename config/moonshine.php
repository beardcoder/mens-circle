<?php

declare(strict_types=1);

use MoonShine\Exceptions\MoonShineNotFoundException;
use MoonShine\Forms\LoginForm;
use MoonShine\Http\Middleware\Authenticate;
use MoonShine\Http\Middleware\SecurityHeadersMiddleware;
use MoonShine\Models\MoonshineUser;
use MoonShine\MoonShineLayout;
use MoonShine\Pages\ProfilePage;

return [
    /**
     * URL prefix for MoonShine panel (separate from Filament)
     * Filament runs on /admin, MoonShine will run on /moonshine
     */
    'route' => [
        'prefix' => env('MOONSHINE_ROUTE_PREFIX', 'moonshine'),
        'single' => false,
        'middlewares' => [],
    ],

    /**
     * Directory where MoonShine resources are stored
     */
    'dir' => 'app/MoonShine',

    /**
     * Namespace for MoonShine resources
     */
    'namespace' => '\App\MoonShine',

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
        'enable' => true,
        'guard' => 'moonshine',
        'guards' => [
            'moonshine' => [
                'driver' => 'session',
                'provider' => 'moonshine',
            ],
        ],
        'providers' => [
            'moonshine' => [
                'driver' => 'eloquent',
                'model' => MoonshineUser::class,
            ],
        ],
    ],

    /**
     * Middleware configuration
     */
    'middleware' => [
        SecurityHeadersMiddleware::class,
        Authenticate::class,
    ],

    /**
     * Use internal authentication or share with Filament
     * For parallel operation, we use separate authentication
     */
    'use_database' => true,
    'use_migrations' => true,
    'use_notifications' => true,

    /**
     * Localization
     */
    'locale' => 'de',
    'locales' => ['de', 'en'],

    /**
     * Layout configuration
     */
    'layout' => MoonShineLayout::class,

    /**
     * Cache configuration
     */
    'cache' => 'array',

    /**
     * Forms configuration
     */
    'forms' => [
        'login' => LoginForm::class,
    ],

    /**
     * Pages configuration
     */
    'pages' => [
        'dashboard' => \App\MoonShine\Pages\Dashboard::class,
        'profile' => ProfilePage::class,
    ],

    /**
     * Exception handling
     */
    'exception' => MoonShineNotFoundException::class,

    /**
     * Use separate disk for uploads
     */
    'disk' => env('MOONSHINE_DISK', 'public'),

    /**
     * Tinker is disabled by default
     */
    'tinker' => false,
];
