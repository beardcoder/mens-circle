<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description('List events with id, slug, title, date and publish state. Filter by scope: "upcoming" (default), "past" or "all".')]
final class ListEvents extends Tool
{
    public function handle(Request $request): Response
    {
        $scope = $request->get('scope') ?? 'upcoming';

        $query = Event::query()->orderBy('event_date');

        match ($scope) {
            'upcoming' => $query->where('event_date', '>=', now()),
            'past' => $query->where('event_date', '<', now())->reorder('event_date', 'desc'),
            default => $query,
        };

        $events = $query->get([
            'id',
            'slug',
            'title',
            'event_date',
            'start_time',
            'end_time',
            'location',
            'max_participants',
            'is_published',
        ]);

        return Response::json(
            $events->map(static fn(Event $event): array => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'event_date' => $event->event_date->toDateString(),
                'start_time' => $event->start_time->format('H:i'),
                'end_time' => $event->end_time->format('H:i'),
                'location' => $event->location,
                'max_participants' => $event->max_participants,
                'is_published' => $event->is_published,
            ])->all(),
        );
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'scope' => $schema
                ->string()
                ->description('Filter scope: "upcoming" (default), "past" or "all".')
                ->enum(['upcoming', 'past', 'all']),
        ];
    }
}
