<?php

declare(strict_types=1);

namespace App\Observers;

use App\Features\Events\Domain\Models\Event;
use Spatie\ResponseCache\Facades\ResponseCache;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->invalidateCache();
    }

    public function updated(Event $event): void
    {
        $this->invalidateCache();
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
        ResponseCache::clear();
        cache()->forget('has_next_event');
    }
}
