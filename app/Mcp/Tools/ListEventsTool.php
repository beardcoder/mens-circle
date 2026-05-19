<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListEventsTool extends Tool
{
    protected string $description = 'List events. Optionally filter by upcoming or past events.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string()->description('Filter events: "upcoming", "past", or leave empty for all.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Event::query()
            ->withCount('activeRegistrations')
            ->orderBy('event_date', 'desc');

        $filter = $request->get('filter');

        if ($filter === 'upcoming') {
            $query->upcoming();
        } elseif ($filter === 'past') {
            $query->where('event_date', '<', now());
        }

        $events = $query->get([
            'id', 'title', 'slug', 'event_date', 'start_time', 'end_time',
            'location', 'max_participants', 'is_published', 'cost_basis',
        ]);

        return Response::json(
            $events->map(static fn(Event $event): array => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'event_date' => $event->event_date->toDateString(),
                'start_time' => $event->start_time->format('H:i'),
                'end_time' => $event->end_time->format('H:i'),
                'location' => $event->location,
                'max_participants' => $event->max_participants,
                'active_registrations' => $event->active_registrations_count,
                'available_spots' => $event->availableSpots,
                'is_published' => $event->is_published,
                'cost_basis' => $event->cost_basis,
            ])->all(),
        );
    }
}
