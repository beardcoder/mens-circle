<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quotes = [
            'Hier kann ich endlich ich selbst sein, ohne Maske und ohne Leistungsdruck.',
            'Der Kreis hat mir gezeigt, dass ich mit meinen Gefühlen nicht alleine bin.',
            'Eine Oase der Ehrlichkeit in einer Welt voller Fassaden.',
            'Hier habe ich gelernt, dass Verletzlichkeit keine Schwäche ist.',
            'Zum ersten Mal habe ich Männer kennengelernt, die wirklich zuhören.',
            'Der Kreis ist ein Raum, in dem ich mich fallen lassen kann.',
            'Ich habe hier mehr über mich gelernt als in Jahren Selbstoptimierung.',
            'Die Tiefe der Gespräche hat mein Leben verändert.',
        ];

        return [
            'quote' => \fake()->randomElement($quotes),
            'author_name' => \fake()->boolean(60) ? \fake()->firstName() : null,
            'role' => \fake()->boolean(70) ? 'Teilnehmer seit '.\fake()->year() : null,
            'is_published' => true,
            'published_at' => now(),
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the testimonial is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the testimonial is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes): array => [
            'author_name' => null,
            'role' => null,
        ]);
    }
}
