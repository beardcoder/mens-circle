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

#[Description('Update an existing event by slug. Only provided fields are changed.')]
final class UpdateEvent extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $slug */
        $slug = $request->get('slug');

        $event = Event::query()->where('slug', $slug)->first();

        if (!$event instanceof Event) {
            return Response::error("Event with slug \"{$slug}\" not found.");
        }

        $payload = array_filter(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'event_date' => $request->get('event_date'),
                'location' => $request->get('location'),
                'street' => $request->get('street'),
                'postal_code' => $request->get('postal_code'),
                'city' => $request->get('city'),
                'max_participants' => $request->get('max_participants'),
                'cost_basis' => $request->get('cost_basis'),
                'is_published' => $request->get('is_published'),
            ],
            static fn($v): bool => $v !== null,
        );

        $eventDate = $request->get('event_date') ?? $event->event_date->toDateString();

        if ($request->get('start_time') !== null) {
            $payload['start_time'] = $eventDate . ' ' . $request->get('start_time') . ':00';
        }

        if ($request->get('end_time') !== null) {
            $payload['end_time'] = $eventDate . ' ' . $request->get('end_time') . ':00';
        }

        $event->update($payload);

        return Response::text("Event \"{$event->slug}\" updated.");
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug of the event to update.')->required(),
            'title' => $schema->string()->description('Event title.'),
            'description' => $schema->string()->description('Event description.'),
            'event_date' => $schema->string()->description('Event date as YYYY-MM-DD.'),
            'start_time' => $schema->string()->description('Start time as HH:MM.'),
            'end_time' => $schema->string()->description('End time as HH:MM.'),
            'location' => $schema->string()->description('Venue name.'),
            'street' => $schema->string()->description('Street and house number.'),
            'postal_code' => $schema->string()->description('Postal code.'),
            'city' => $schema->string()->description('City.'),
            'max_participants' => $schema->integer()->description('Maximum participants.'),
            'cost_basis' => $schema->string()->description('Cost notice.'),
            'is_published' => $schema->boolean()->description('Publish state.'),
        ];
    }
}
