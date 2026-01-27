<?php

declare(strict_types=1);

namespace Database\Factories;

use Faker\Factory as FakerFactory;

use App\Enums\NewsletterStatus;
use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsletter>
 */
class NewsletterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = FakerFactory::create();

        return [
            'subject' => $faker->sentence(),
            'content' => $faker->paragraphs(3, true),
            'status' => NewsletterStatus::Draft,
            'sent_at' => null,
            'recipient_count' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NewsletterStatus::Draft,
            'sent_at' => null,
            'recipient_count' => null,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => NewsletterStatus::Sending,
            'sent_at' => null,
            'recipient_count' => null,
        ]);
    }

    public function sent(): static
    {
        $faker = FakerFactory::create();
        
        return $this->state(fn (array $attributes): array => [
            'status' => NewsletterStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $faker->numberBetween(10, 100),
        ]);
    }
}
