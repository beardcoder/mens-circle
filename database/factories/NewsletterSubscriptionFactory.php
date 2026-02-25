<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NewsletterSubscription;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsletterSubscription>
 */
class NewsletterSubscriptionFactory extends Factory
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
            'token' => Str::random(64),
            'subscribed_at' => now(),
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function forParticipant(Participant $participant): static
    {
        return $this->state(fn(array $attributes): array => [
            'participant_id' => $participant->id,
        ]);
    }

    public function unconfirmed(): static
    {
        return $this->state(fn(array $attributes): array => [
            'confirmed_at' => null,
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn(array $attributes): array => [
            'unsubscribed_at' => now(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes): array => [
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }
}
