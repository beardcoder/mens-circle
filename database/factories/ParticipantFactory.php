<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Participant;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
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
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'email' => $faker->unique()->safeEmail(),
            'phone' => $faker->optional(0.3)->phoneNumber(),
        ];
    }

    public function withPhone(): static
    {
        $faker = FakerFactory::create();

        return $this->state(fn (array $attributes): array => [
            'phone' => $faker->phoneNumber(),
        ]);
    }
}
