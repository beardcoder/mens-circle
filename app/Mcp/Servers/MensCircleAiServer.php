<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateEventDraftTool;
use App\Mcp\Tools\GenerateNewsletterTool;
use App\Mcp\Tools\GeneratePageContentTool;
use App\Mcp\Tools\GetEventTool;
use App\Mcp\Tools\GetGeneralSettingsTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListEventsTool;
use App\Mcp\Tools\ListPagesTool;
use App\Mcp\Tools\ListPendingTestimonialsTool;
use App\Mcp\Tools\ModerateTestimonialTool;
use App\Mcp\Tools\PlanEventTool;
use App\Mcp\Tools\PublishEventTool;
use App\Mcp\Tools\PublishPageTool;
use App\Mcp\Tools\SendNewsletterTool;
use App\Mcp\Tools\SiteOverviewTool;
use App\Mcp\Tools\UpdateEventTool;
use App\Mcp\Tools\UpdateGeneralSettingsTool;
use App\Mcp\Tools\UpdatePageBlocksTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Mens Circle AI')]
#[Version('1.0.0')]
#[Instructions('Nutze diese Werkzeuge, um Events, Seiten, Newsletter, Testimonials und Einstellungen strukturiert auf Deutsch zu verwalten. Schreibende Aktionen benötigen immer eine explizite Bestätigung.')]
final class MensCircleAiServer extends Server
{
    protected array $tools = [
        SiteOverviewTool::class,
        ListEventsTool::class,
        GetEventTool::class,
        PlanEventTool::class,
        CreateEventDraftTool::class,
        UpdateEventTool::class,
        PublishEventTool::class,
        ListPagesTool::class,
        GetPageTool::class,
        GeneratePageContentTool::class,
        UpdatePageBlocksTool::class,
        PublishPageTool::class,
        GenerateNewsletterTool::class,
        SendNewsletterTool::class,
        ListPendingTestimonialsTool::class,
        ModerateTestimonialTool::class,
        GetGeneralSettingsTool::class,
        UpdateGeneralSettingsTool::class,
    ];
}
