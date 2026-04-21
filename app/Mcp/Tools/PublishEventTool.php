<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\SetAiEventPublicationState;
use App\Models\Event;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

final class PublishEventTool extends Tool
{
    protected string $name = 'publish_event';

    protected string $description = 'Veröffentlicht oder versteckt ein Event. Erwartet event_id, is_published und confirm=true.';

    public function __construct(
        private readonly SetAiEventPublicationState $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        if (! $request->boolean('confirm')) {
            throw new RuntimeException('Zum Veröffentlichen ist confirm=true erforderlich.');
        }

        $event = Event::query()->findOrFail($request->integer('event_id'));
        $event = $this->action->execute($event, $request->boolean('is_published', true));
        $event->loadCount('activeRegistrations');

        return Response::structured([
            'data' => $this->formatter->event($event),
        ]);
    }
}
