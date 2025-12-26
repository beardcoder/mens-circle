<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Sentry\Laravel\Integration;
use Spatie\ResponseCache\Middlewares\CacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cache.response' => CacheResponse::class,
        ]);

        $middleware->group('public-cache', [
            SetCacheHeaders::using([
                'public' => true,
                'max_age' => 300,
                's_maxage' => 600,
                'stale_while_revalidate' => 600,
                'etag' => true,
            ]),
            CacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
