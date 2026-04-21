<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Event;
use App\Services\Ai\AiAuditLogger;
use Carbon\CarbonImmutable;

final readonly class UpdateAiEvent
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(Event $event, array $data): Event
    {
        $payload = $data;

        if (isset($payload['event_date'], $payload['start_time'])) {
            $payload['start_time'] = CarbonImmutable::parse((string) ($payload['event_date'] . ' ' . $payload['start_time']));
        }

        if (isset($payload['event_date'], $payload['end_time'])) {
            $payload['end_time'] = CarbonImmutable::parse((string) ($payload['event_date'] . ' ' . $payload['end_time']));
        }

        if (isset($payload['event_date'])) {
            $payload['event_date'] = CarbonImmutable::parse((string) $payload['event_date']);
        }

        $event->update($payload);

        $this->auditLogger->log('ai.event.updated', [
            'event_id' => $event->id,
            'updated_fields' => array_keys($data),
        ]);

        return $event->fresh(['media']);
    }
}
