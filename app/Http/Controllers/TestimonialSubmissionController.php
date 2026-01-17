<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TestimonialSubmissionRequest;
use App\Models\Testimonial;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TestimonialSubmissionController extends Controller
{
    public function show(): View
    {
        return view('testimonial-form');
    }

    public function submit(TestimonialSubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            Testimonial::create([
                'quote' => $validated['quote'],
                'author_name' => $validated['author_name'] ?? null,
                'email' => $validated['email'],
                'role' => $validated['role'] ?? null,
                'is_published' => false,
                'published_at' => null,
                'sort_order' => 0,
            ]);

            $firstName = isset($validated['author_name']) ? explode(' ', $validated['author_name'])[0] : null;
            $message = $firstName
                ? sprintf('Vielen Dank, %s! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.', $firstName)
                : 'Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht.';

            return response()->json(['success' => true, 'message' => $message]);
        } catch (Exception $exception) {
            Log::error('Failed to submit testimonial', [
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Es gab einen Fehler beim Einreichen deiner Erfahrung. Bitte versuche es später erneut.',
            ], 500);
        }
    }
}
