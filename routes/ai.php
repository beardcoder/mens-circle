<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAiAccess;
use App\Mcp\Servers\MensCircleAiServer;
use Laravel\Mcp\Facades\Mcp;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

Mcp::web('/mcp', MensCircleAiServer::class)
    ->middleware([EnsureAiAccess::class, DoNotCacheResponse::class, 'throttle:60,1']);

Mcp::local('mens-circle-ai', MensCircleAiServer::class);
