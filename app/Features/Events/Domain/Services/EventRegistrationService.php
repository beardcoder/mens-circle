<?php

declare(strict_types=1);

namespace App\Features\Events\Domain\Services;

use App\Features\Events\Domain\Enums\RegistrationStatus;
use App\Features\Events\Domain\Models\Event;
use App\Features\Events\Domain\Models\Registration;
use App\Domain\Models\Participant;
use Spatie\ResponseCache\Facades\ResponseCache;

final readonly class EventRegistrationService
{
    public function __construct(
        private EventNotificationService $notificationService
    ) {
    }

    /**
     * Register a participant for an event.
     *
     * @param array<string, mixed> $data
     * @throws \RuntimeException
     */
    public function register(Event $event, array $data): Registration
    {
        $this->validateEventAvailability($event);

        $participant = $this->findOrUpdateParticipant($data);
        $registration = $this->createOrRestoreRegistration($event, $participant);

        $registration->setRelation('participant', $participant);

        ResponseCache::clear();

        $this->notificationService->sendRegistrationConfirmation($event, $registration);

        return $registration;
    }

    /**
     * Validate that the event is available for registration.
     *
     * @throws \RuntimeException
     */
    public function validateEventAvailability(Event $event): void
    {
        if (! $event->is_published) {
            throw new \RuntimeException('Diese Veranstaltung ist nicht verfügbar.');
        }

        if ($event->isPast) {
            throw new \RuntimeException('Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.');
        }

        if ($event->isFull) {
            throw new \RuntimeException('Diese Veranstaltung ist leider bereits ausgebucht.');
        }
    }

    /**
     * Find existing participant or create/update with new data.
     *
     * @param array<string, mixed> $data
     */
    private function findOrUpdateParticipant(array $data): Participant
    {
        $participant = Participant::findOrCreateByEmail($data['email'], [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone_number'] ?? null,
        ]);

        $participant->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone_number'] ?? $participant->phone,
        ]);

        return $participant;
    }

    /**
     * Create a new registration or restore a previously cancelled one.
     *
     * @throws \RuntimeException
     */
    private function createOrRestoreRegistration(Event $event, Participant $participant): Registration
    {
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
