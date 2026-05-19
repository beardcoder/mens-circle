<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Event;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateEventTool extends Tool
{
    protected string $description = 'Update an event. Provide the event ID and only the fields you want to change.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required()->description('The numeric event ID to update.'),
            'title' => $schema->string()->description('New event title.'),
            'description' => $schema->string()->description('New event description (HTML allowed).'),
            'event_date' => $schema->string()->description('Event date in YYYY-MM-DD format.'),
            'start_time' => $schema->string()->description('Start time in HH:MM format.'),
            'end_time' => $schema->string()->description('End time in HH:MM format.'),
            'location' => $schema->string()->description('Venue name.'),
            'street' => $schema->string()->description('Street address.'),
            'postal_code' => $schema->string()->description('Postal code.'),
            'city' => $schema->string()->description('City.'),
            'max_participants' => $schema->integer()->description('Maximum number of participants.'),
            'cost_basis' => $schema->string()->description('Cost / price basis for the event.'),
            'is_published' => $schema->boolean()->description('Whether the event should be published.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'event_date' => ['sometimes', 'date_format:Y-m-d'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'location' => ['sometimes', 'string', 'max:255'],
            'street' => ['sometimes', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'city' => ['sometimes', 'string', 'max:255'],
            'max_participants' => ['sometimes', 'integer', 'min:1'],
            'cost_basis' => ['sometimes', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $event = Event::query()->find($data['id']);

        if ($event === null) {
            return Response::error("Event [{$data['id']}] not found.");
        }

        $fields = ['title', 'description', 'event_date', 'start_time', 'end_time', 'location',
            'street', 'postal_code', 'city', 'max_participants', 'cost_basis', 'is_published'];

        $updates = array_filter(
            array_intersect_key($data, array_flip($fields)),
            static fn($v): bool => $v !== null,
        );

        $event->update($updates);

        return Response::text("Event [{$event->title}] updated successfully.");
    }
}
