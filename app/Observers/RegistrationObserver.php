<?php

declare(strict_types=1);

namespace App\Observers;

use Throwable;
use App\Enums\RegistrationStatus;
use App\Mail\WaitlistPromotion;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        rescue(
            fn() => Mail::queue(new WaitlistPromotion($nextWaitlisted, $nextWaitlisted->event)),
            fn(Throwable $throwable) => Log::error('Failed to send waitlist promotion email', [
                'registration_id' => $nextWaitlisted->id,
                'error' => $throwable->getMessage(),
            ]),
            report: false,
        );
    }
}
