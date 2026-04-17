<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\EventRegistrationConfirmed;
use App\Notifications\EventRegistrationReceived;
use App\Notifications\WaitlistRegistrationConfirmed;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

final readonly class RegisterForEvent
{
    /**
     * @param array{email: string, first_name: string, last_name: string, phone_number?: ?string} $data
     *
     * @throws RuntimeException if the participant is already registered or waitlisted
     */
    public function execute(Event $event, array $data): Registration
    {
        $isWaitlist = $event->isFull;

        $participant = Participant::updateOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone_number'] ?? null,
            ],
        );

        $registration = $this->createOrRestoreRegistration($event, $participant, $isWaitlist);
        $registration->setRelation('participant', $participant);

        $this->notify($registration, $event, $participant, $isWaitlist);

        return $registration;
    }

    private function createOrRestoreRegistration(Event $event, Participant $participant, bool $isWaitlist): Registration
    {
        $existing = Registration::withTrashed()
            ->where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing && !$existing->trashed()) {
            throw new RuntimeException(
                $existing->status === RegistrationStatus::Waitlist
                    ? 'Du bist bereits auf der Warteliste für diese Veranstaltung.'
                    : 'Du bist bereits für diese Veranstaltung angemeldet.',
            );
        }

        $status = $isWaitlist ? RegistrationStatus::Waitlist : RegistrationStatus::Registered;

        if ($existing) {
            $existing->restore();
            $existing->update([
                'status' => $status,
                'registered_at' => now(),
                'cancelled_at' => null,
            ]);

            return $existing;
        }

        return Registration::create([
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'status' => $status,
            'registered_at' => now(),
        ]);
    }

    private function notify(Registration $registration, Event $event, Participant $participant, bool $isWaitlist): void
    {
        if ($isWaitlist) {
            $participant->notify(new WaitlistRegistrationConfirmed($registration, $event));
            Notification::send(User::all(), new EventRegistrationReceived($registration, $event, $participant, isWaitlist: true));

            return;
        }

        $participant->notify(new EventRegistrationConfirmed($registration, $event));
        Notification::send(User::all(), new EventRegistrationReceived($registration, $event, $participant));
    }
}
