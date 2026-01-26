<?php

declare(strict_types=1);

namespace App\Features\Testimonials\Domain\Services;

use App\Features\Testimonials\Domain\Models\Testimonial;

final readonly class TestimonialService
{
    /**
     * Submit a new testimonial.
     *
     * @param array<string, mixed> $data
     */
    public function submit(array $data): Testimonial
    {
        return Testimonial::create([
            'quote' => $data['quote'],
            'author_name' => $data['author_name'] ?? null,
            'email' => $data['email'],
            'role' => $data['role'] ?? null,
            'is_published' => false,
            'published_at' => null,
            'sort_order' => 0,
        ]);
    }

    /**
     * Build success message for testimonial submission.
     *
     * @param array<string, mixed> $data
     */
    public function buildSuccessMessage(array $data): string
    {
        if (! isset($data['author_name'])) {
            return 'Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.';
        }

        $firstName = explode(' ', $data['author_name'])[0];

        return sprintf(
            'Vielen Dank, %s! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.',
            $firstName
        );
    }
}
