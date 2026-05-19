<?php

declare(strict_types=1);

use App\Mcp\CmsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', CmsServer::class)
    ->middleware(['mcp.token']);
