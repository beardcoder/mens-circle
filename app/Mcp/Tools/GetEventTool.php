<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class GetEventTool extends Tool
{
    protected string $name = 'get_event';

    protected string $description = 'Gibt ein einzelnes Event inklusive Anmeldungen zurück. Erwartet event_id.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        $event = Event::query()->withCount('activeRegistrations')->findOrFail((int) $request->get('event_id'));

        return Response::structured([
            'data' => $this->formatter->event($event, includeRegistrations: true),
        ]);
    }
}
