<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class ListEventsTool extends Tool
{
    protected string $name = 'list_events';

    protected string $description = 'Listet Events auf. Optional: upcoming=true und published=true.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $query = Event::query()->withCount('activeRegistrations')->orderBy('event_date');

        if ($request->boolean('upcoming', true)) {
            $query->upcoming();
        }

        if ($request->boolean('published', false)) {
            $query->published();
        }

        return Response::structured([
            'data' => $this->formatter->events($query->get()),
        ]);
    }
}
