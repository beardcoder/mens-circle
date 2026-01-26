<?php

declare(strict_types=1);

namespace App\Features\Testimonials\Http\Controllers;

use App\Features\Testimonials\Domain\Services\TestimonialService;
use App\Features\Testimonials\Http\Requests\TestimonialSubmissionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TestimonialSubmissionController extends Controller
{
    public function __construct(
        private readonly TestimonialService $testimonialService
    ) {
    }

    public function show(): View
    {
        return view('testimonial-form');
    }

    public function submit(TestimonialSubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->testimonialService->submit($validated);

        $message = $this->testimonialService->buildSuccessMessage($validated);

        return response()->json(['success' => true, 'message' => $message]);
    }
}
