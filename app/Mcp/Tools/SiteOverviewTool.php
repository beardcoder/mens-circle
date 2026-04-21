<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\BuildAiSiteContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class SiteOverviewTool extends Tool
{
    protected string $name = 'site_overview';

    protected string $description = 'Gibt einen strukturierten Überblick über Website, Inhalte, nächste Veranstaltung und verfügbare KI-Endpunkte zurück.';

    public function __construct(
        private readonly BuildAiSiteContext $action,
    ) {}

    public function handle(Request $request): Response
    {
        return Response::structured($this->action->execute());
    }
}
