<?php

declare(strict_types=1);

use App\Mcp\Servers\MensCircleAiServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('mens-circle-ai', MensCircleAiServer::class);
