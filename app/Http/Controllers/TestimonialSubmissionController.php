<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TestimonialSubmissionRequest;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class TestimonialSubmissionController
{
    public function show(): View
    {
        return view('testimonial-form');
    }

    public function submit(TestimonialSubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Testimonial::create([
            'quote' => $validated['quote'],
            'author_name' => $validated['author_name'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'] ?? null,
            'is_published' => false,
            'published_at' => null,
            'sort_order' => 0,
        ]);

        $message = $this->buildSuccessMessage($validated);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function buildSuccessMessage(array $validated): string
    {
        if (!isset($validated['author_name']) || $validated['author_name'] === '') {
            return 'Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.';
        }

        /** @var string $authorName */
        $authorName = $validated['author_name'];
        $firstName = explode(' ', $authorName)[0];

        return "Vielen Dank, {$firstName}! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.";
    }
}
