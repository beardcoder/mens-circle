<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Event;
use App\Services\Ai\AiAuditLogger;
use Carbon\CarbonImmutable;

final readonly class CreateAiEventDraft
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Event
    {
        $eventDate = is_string($data['event_date']) ? $data['event_date'] : '';
        $startTime = is_string($data['start_time']) ? $data['start_time'] : '';
        $endTime = is_string($data['end_time']) ? $data['end_time'] : '';

        $event = Event::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_date' => CarbonImmutable::parse($eventDate),
            'start_time' => CarbonImmutable::parse($eventDate . ' ' . $startTime),
            'end_time' => CarbonImmutable::parse($eventDate . ' ' . $endTime),
            'location' => $data['location'],
            'street' => $data['street'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'city' => $data['city'] ?? null,
            'location_details' => $data['location_details'] ?? null,
            'max_participants' => $data['max_participants'],
            'cost_basis' => $data['cost_basis'],
            'is_published' => false,
        ]);

        $this->auditLogger->log('ai.event.created', [
            'event_id' => $event->id,
            'title' => $event->title,
        ]);

        return $event;
    }
}
