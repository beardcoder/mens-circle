<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Event;
use App\Services\Ai\AiAuditLogger;

final readonly class SetAiEventPublicationState
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    public function execute(Event $event, bool $isPublished): Event
    {
        $event->update([
            'is_published' => $isPublished,
        ]);

        $this->auditLogger->log('ai.event.publication.updated', [
            'event_id' => $event->id,
            'is_published' => $isPublished,
        ]);

        return $event->fresh();
    }
}
