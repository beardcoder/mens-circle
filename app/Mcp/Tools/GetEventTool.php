<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetEventTool extends Tool
{
    protected string $description = 'Get full details for a single event by ID or slug, including registration count.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric event ID.'),
            'slug' => $schema->string()->description('The event slug (alternative to ID).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        $query = Event::query()->withCount('activeRegistrations');

        $event = $id !== null
            ? $query->find($id)
            : $query->where('slug', $slug)->first();

        if ($event === null) {
            return Response::error('Event not found.');
        }

        return Response::json([
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'description' => $event->description,
            'event_date' => $event->event_date->toDateString(),
            'start_time' => $event->start_time->format('H:i'),
            'end_time' => $event->end_time->format('H:i'),
            'location' => $event->location,
            'street' => $event->street,
            'postal_code' => $event->postal_code,
            'city' => $event->city,
            'full_address' => $event->fullAddress,
            'location_details' => $event->location_details ?? null,
            'max_participants' => $event->max_participants,
            'active_registrations' => $event->active_registrations_count,
            'available_spots' => $event->availableSpots,
            'is_full' => $event->isFull,
            'is_past' => $event->isPast,
            'is_published' => $event->is_published,
            'cost_basis' => $event->cost_basis,
        ]);
    }
}
