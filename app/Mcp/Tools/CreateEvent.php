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

#[Description('Create a new event. Times are 24h "HH:MM"; event_date is YYYY-MM-DD; slug is auto-generated from the date if omitted.')]
class CreateEvent extends Tool
{
    public function handle(Request $request): Response
    {
        $event = Event::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'event_date' => $request->get('event_date'),
            'start_time' => $request->get('event_date') . ' ' . $request->get('start_time') . ':00',
            'end_time' => $request->get('event_date') . ' ' . $request->get('end_time') . ':00',
            'location' => $request->get('location'),
            'street' => $request->get('street'),
            'postal_code' => $request->get('postal_code'),
            'city' => $request->get('city'),
            'max_participants' => $request->get('max_participants'),
            'cost_basis' => $request->get('cost_basis'),
            'is_published' => $request->get('is_published') ?? false,
        ]);

        return Response::json([
            'id' => $event->id,
            'slug' => $event->slug,
            'title' => $event->title,
            'event_date' => $event->event_date->toDateString(),
            'is_published' => $event->is_published,
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Event title.')->required(),
            'description' => $schema->string()->description('Optional event description (HTML/plain).'),
            'event_date' => $schema->string()->description('Event date in YYYY-MM-DD format.')->required(),
            'start_time' => $schema->string()->description('Start time as HH:MM (24h).')->required(),
            'end_time' => $schema->string()->description('End time as HH:MM (24h).')->required(),
            'location' => $schema->string()->description('Venue name.'),
            'street' => $schema->string()->description('Street and house number.'),
            'postal_code' => $schema->string()->description('Postal code.'),
            'city' => $schema->string()->description('City.'),
            'max_participants' => $schema->integer()->description('Maximum number of participants.')->required(),
            'cost_basis' => $schema->string()->description('Free-form cost notice shown to participants (e.g. "Spende erwünscht").'),
            'is_published' => $schema->boolean()->description('Publish immediately. Defaults to false (draft).'),
        ];
    }
}
