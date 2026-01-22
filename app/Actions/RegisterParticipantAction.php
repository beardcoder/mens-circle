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
        // Find or create participant
        $participant = Participant::findOrCreateByEmail($data['email'], [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone_number'] ?? null,
        ]);

        // Update participant data if changed
        $participant->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone_number'] ?? $participant->phone,
        ]);

        // Check for existing registration (including soft-deleted ones)
        $existingRegistration = Registration::withTrashed()
            ->where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existingRegistration && ! $existingRegistration->trashed()) {
            throw new \RuntimeException('Du bist bereits für diese Veranstaltung angemeldet.');
        }

        if ($existingRegistration) {
            $existingRegistration->restore();
            $existingRegistration->update([
                'status' => RegistrationStatus::Registered->value,
                'registered_at' => now(),
                'cancelled_at' => null,
            ]);
            $registration = $existingRegistration;
        } else {
            $registration = Registration::create([
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'status' => RegistrationStatus::Registered->value,
                'registered_at' => now(),
            ]);
        }

        ResponseCache::clear();

        // Send confirmations
        $event->sendRegistrationConfirmation($registration);

        return $registration;
    }
}
