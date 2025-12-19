<?php

namespace App\Observers;

use App\Models\Event;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        cache()->forget('event.next');
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        cache()->forget('event.next');
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        cache()->forget('event.next');
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        cache()->forget('event.next');
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        cache()->forget('event.next');
    }
}
