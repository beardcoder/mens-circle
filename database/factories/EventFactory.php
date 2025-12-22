<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $startTime = fake()->dateTimeBetween('18:00', '19:00');
        $endTime = (clone $startTime)->modify('+2 hours');

        return [
            'title' => 'Männerkreis '.fake()->city(),
            'description' => fake()->paragraphs(3, true),
            'event_date' => $eventDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => fake()->city(),
            'location_details' => fake()->address(),
            'max_participants' => 8,
            'cost_basis' => 'Auf Spendenbasis',
            'is_published' => false,
        ];
    }

    /**
     * Indicate that the event is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the event is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Set the event date to tomorrow (24 hours from now).
     */
    public function tomorrow(): static
    {
        $tomorrow = now()->addDay();

        return $this->state(fn (array $attributes) => [
            'event_date' => $tomorrow,
            'start_time' => $tomorrow->copy()->setTime(19, 0),
            'end_time' => $tomorrow->copy()->setTime(21, 0),
        ]);
    }

    /**
     * Set the event date to a specific date.
     */
    public function onDate(\DateTime|\DateTimeInterface $date): static
    {
        return $this->state(fn (array $attributes) => [
            'event_date' => $date,
        ]);
    }
}
