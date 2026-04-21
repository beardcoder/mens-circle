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
        $event = Event::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_date' => CarbonImmutable::parse((string) $data['event_date']),
            'start_time' => CarbonImmutable::parse((string) ($data['event_date'] . ' ' . $data['start_time'])),
            'end_time' => CarbonImmutable::parse((string) ($data['event_date'] . ' ' . $data['end_time'])),
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
