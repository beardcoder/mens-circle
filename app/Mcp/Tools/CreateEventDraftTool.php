<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\CreateAiEventDraft;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class CreateEventDraftTool extends Tool
{
    protected string $name = 'create_event_draft';

    protected string $description = 'Speichert einen neuen Event-Entwurf. Erwartet die Event-Felder wie title, event_date, start_time, end_time, location, max_participants und cost_basis.';

    public function __construct(
        private readonly CreateAiEventDraft $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $event = $this->action->execute($request->all());
        $event->loadCount('activeRegistrations');

        return Response::structured([
            'data' => $this->formatter->event($event),
        ]);
    }
}
