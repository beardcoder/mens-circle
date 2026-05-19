<?php

declare(strict_types=1);

use App\Mcp\Servers\ContentServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp/content', ContentServer::class)->middleware('auth:api');
