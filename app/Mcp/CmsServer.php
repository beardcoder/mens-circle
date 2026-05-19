<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Mcp\Tools\GetEventTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListEventsTool;
use App\Mcp\Tools\ListPagesTool;
use App\Mcp\Tools\ListTestimonialsTool;
use App\Mcp\Tools\UpdateEventTool;
use App\Mcp\Tools\UpdatePageTool;
use App\Mcp\Tools\UpdateTestimonialTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('mens-circle-cms')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    This MCP server gives you access to the Männerkreis Niederbayern / Straubing CMS.
    You can list, read and update pages, events and testimonials.
    All write operations are immediately persisted to the database.
    Use the list tools first to discover available resources, then get or update specific ones by ID.
    MARKDOWN)]
class CmsServer extends Server
{
    /** @var array<int, class-string<\Laravel\Mcp\Server\Tool>> */
    protected array $tools = [
        ListPagesTool::class,
        GetPageTool::class,
        UpdatePageTool::class,
        ListEventsTool::class,
        GetEventTool::class,
        UpdateEventTool::class,
        ListTestimonialsTool::class,
        UpdateTestimonialTool::class,
    ];
}
