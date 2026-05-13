<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Notifications\WaitlistParticipantPromoted;

final class RegistrationObserver
{
    public bool $afterCommit = true;

    public function updated(Registration $registration): void
    {
        if (!$registration->wasChanged('status')) {
            return;
        }

        if ($registration->status !== RegistrationStatus::Cancelled) {
            return;
        }

        $this->promoteFromWaitlist($registration);
    }

    private function promoteFromWaitlist(Registration $cancelledRegistration): void
    {
        $nextWaitlisted = Registration::query()
            ->with(['participant', 'event'])
            ->where('event_id', $cancelledRegistration->event_id)
            ->waitlisted()
            ->orderBy('registered_at')
            ->first();

        if (!$nextWaitlisted) {
            return;
        }

        $nextWaitlisted->promote();

        $nextWaitlisted->participant->notify(
            new WaitlistParticipantPromoted($nextWaitlisted, $nextWaitlisted->event),
        );
    }
}
