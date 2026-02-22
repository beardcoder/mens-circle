<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\RegistrationStatus;
use App\Mail\WaitlistPromotion;
use App\Models\Registration;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationObserver
{
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

        try {
            Mail::queue(new WaitlistPromotion($nextWaitlisted, $nextWaitlisted->event));
        } catch (Exception $exception) {
            Log::error('Failed to send waitlist promotion email', [
                'registration_id' => $nextWaitlisted->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
