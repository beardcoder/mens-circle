<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\PlanAiEvent;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class PlanEventTool extends Tool
{
    protected string $name = 'plan_event';

    protected string $description = 'Erstellt aus einem deutschen Prompt einen strukturierten Event-Entwurf. Erwartet prompt.';

    public function __construct(
        private readonly PlanAiEvent $action,
    ) {}

    public function handle(Request $request): Response
    {
        return Response::structured([
            'data' => $this->action->execute((string) $request->get('prompt', '')),
        ]);
    }
}
