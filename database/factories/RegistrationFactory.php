<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'event_id' => Event::factory(),
            'status' => RegistrationStatus::Registered->value,
            'registered_at' => now(),
            'cancelled_at' => null,
        ];
    }

    public function registered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RegistrationStatus::Registered->value,
            'registered_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    public function waitlist(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RegistrationStatus::Waitlist->value,
            'registered_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RegistrationStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    public function attended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RegistrationStatus::Attended->value,
            'registered_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_id' => $event->id,
        ]);
    }

    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn (array $attributes): array => [
            'participant_id' => $participant->id,
        ]);
    }
}
