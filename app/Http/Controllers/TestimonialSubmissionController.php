<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SubmitTestimonialAction;
use App\Http\Requests\TestimonialSubmissionRequest;
use Exception;
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

        try {
            $action->execute($validated);

            $firstName = isset($validated['author_name']) ? explode(' ', $validated['author_name'])[0] : null;
            $message = $firstName
                ? sprintf('Vielen Dank, %s! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.', $firstName)
                : 'Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.';

            return response()->json(['success' => true, 'message' => $message]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Es gab einen Fehler beim Einreichen deiner Erfahrung. Bitte versuche es später erneut.',
            ], 500);
        }
    }
}
