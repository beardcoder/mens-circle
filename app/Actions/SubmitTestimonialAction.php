<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Testimonial;

class SubmitTestimonialAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Testimonial
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
}
