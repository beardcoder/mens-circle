<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PhpStaticAnalysis\Attributes\Returns;
use PhpStaticAnalysis\Attributes\TemplateExtends;

#[TemplateExtends('\Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>')]
class ParticipantFactory extends Factory
{
    #[Returns('array<string, mixed>')]
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.3)->phoneNumber(),
        ];
    }

    public function withPhone(): static
    {
        return $this->state(fn (array $attributes): array => [
            'phone' => fake()->phoneNumber(),
        ]);
    }
}
