<?php

namespace App\Observers;

use App\Models\EventRegistration;
use Spatie\ResponseCache\Facades\ResponseCache;

class EventRegistrationObserver
{
    /**
     * Handle the EventRegistration "created" event.
     */
    public function created(EventRegistration $registration): void
    {
        $this->clearEventCache();
    }

    /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $registration): void
    {
        $this->clearEventCache();
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $registration): void
    {
        $this->clearEventCache();
    }

    /**
     * Handle the EventRegistration "restored" event.
     */
    public function restored(EventRegistration $registration): void
    {
        $this->clearEventCache();
    }

    /**
     * Handle the EventRegistration "force deleted" event.
     */
    public function forceDeleted(EventRegistration $registration): void
    {
        $this->clearEventCache();
    }

    /**
     * Clear all event-related cache when registrations change.
     * This is important because event pages display available spots.
     */
    protected function clearEventCache(): void
    {
        cache()->forget('event.next');
        cache()->forget('has_next_event');

        // Clear full HTTP response cache
        ResponseCache::clear();
    }
}
