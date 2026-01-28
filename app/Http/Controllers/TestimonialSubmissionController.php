<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SubmitTestimonialAction;
use App\Http\Requests\TestimonialSubmissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TestimonialSubmissionController extends Controller
{
    public function show(): View
    {
        return view('testimonial-form');
    }

    public function submit(TestimonialSubmissionRequest $request, SubmitTestimonialAction $action): JsonResponse
    {
        $validated = $request->validated();
        $action->execute($validated);

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
        if (empty($validated['author_name'])) {
            return 'Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.';
        }

        $firstName = explode(' ', $validated['author_name'])[0];

        return "Vielen Dank, {$firstName}! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.";
    }
}
