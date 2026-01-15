<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class EventRegistrationData
{
    public function __construct(
        public int $eventId,
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $phoneNumber = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            eventId: (int) $data['event_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            phoneNumber: $data['phone_number'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'privacy_accepted' => true,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ];
    }
}
