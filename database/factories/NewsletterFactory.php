<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NewsletterStatus;
use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsletter>
 */
#[UseModel(Newsletter::class)]
class NewsletterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => NewsletterStatus::Draft,
            'sent_at' => null,
            'recipient_count' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => NewsletterStatus::Draft,
            'sent_at' => null,
            'recipient_count' => null,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => NewsletterStatus::Sending,
            'sent_at' => null,
            'recipient_count' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes): array => [
            'status' => NewsletterStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $this->faker->numberBetween(10, 100),
        ]);
    }
}
