<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->invalidateCache();
    }

    public function updated(Event $event): void
    {
        // Invalidate cache if publication status or event date changed
        if ($event->isDirty(['is_published', 'event_date'])) {
            $this->invalidateCache();
        }
    }

    public function deleted(Event $event): void
    {
        $this->invalidateCache();
    }

    public function restored(Event $event): void
    {
        $this->invalidateCache();
    }

    protected function invalidateCache(): void
    {
        Cache::forget('has_next_event');
    }
}
