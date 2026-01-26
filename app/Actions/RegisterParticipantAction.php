<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Spatie\ResponseCache\Facades\ResponseCache;

class RegisterParticipantAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(Event $event, array $data): Registration
    {
        $participant = $this->findOrUpdateParticipant($data);
        $registration = $this->createOrRestoreRegistration($event, $participant);

        $registration->setRelation('participant', $participant);

        ResponseCache::clear();

        $event->sendRegistrationConfirmation($registration);

        return $registration;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function findOrUpdateParticipant(array $data): Participant
    {
        return Participant::updateOrCreate(
            [
'email' => $data['email']
],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone_number'] ?? null,
            ]
        );
    }

    private function createOrRestoreRegistration(Event $event, Participant $participant): Registration
    {
        $existingRegistration = Registration::withTrashed()
            ->where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existingRegistration && !$existingRegistration->trashed()) {
            throw new \RuntimeException('Du bist bereits für diese Veranstaltung angemeldet.');
        }

        if ($existingRegistration) {
            $existingRegistration->restore();
            $existingRegistration->update([
                'status' => RegistrationStatus::Registered,
                'registered_at' => now(),
                'cancelled_at' => null,
            ]);

            return $existingRegistration;
        }

        return Registration::create([
            'participant_id' => $participant->id,
            'event_id' => $event->id,
            'status' => RegistrationStatus::Registered,
            'registered_at' => now(),
        ]);
    }
}
