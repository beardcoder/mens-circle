<?php

namespace App\Observers;

use App\Models\EventRegistration;
use Illuminate\Support\Facades\Cache;

class EventRegistrationObserver
{
    public function created(EventRegistration $eventRegistration): void
    {
        $this->invalidateCache();
    }

    public function updated(EventRegistration $eventRegistration): void
    {
        $this->invalidateCache();
    }

    public function deleted(EventRegistration $eventRegistration): void
    {
        $this->invalidateCache();
    }

    public function restored(EventRegistration $eventRegistration): void
    {
        $this->invalidateCache();
    }

    protected function invalidateCache(): void
    {
        Cache::forget('has_next_event');
    }
}
