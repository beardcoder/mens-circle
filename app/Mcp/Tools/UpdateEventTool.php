<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\UpdateAiEvent;
use App\Models\Event;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class UpdateEventTool extends Tool
{
    protected string $name = 'update_event';

    protected string $description = 'Aktualisiert ein bestehendes Event. Erwartet event_id und die zu ändernden Felder.';

    public function __construct(
        private readonly UpdateAiEvent $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        $event = Event::query()->findOrFail((int) $request->get('event_id'));
        $payload = $request->all();
        unset($payload['event_id']);

        $event = $this->action->execute($event, $payload);
        $event->loadCount('activeRegistrations');

        return Response::structured([
            'data' => $this->formatter->event($event),
        ]);
    }
}
