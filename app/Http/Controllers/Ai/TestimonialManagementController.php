<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\ModerateAiTestimonial;
use App\Http\Requests\Ai\ConfirmModerationRequest;
use App\Models\Testimonial;
use App\Services\Ai\AiDataFormatter;
use Illuminate\Http\JsonResponse;

final class TestimonialManagementController
{
    public function pending(AiDataFormatter $formatter): JsonResponse
    {
        $testimonials = Testimonial::query()->where('is_published', false)->orderByDesc('created_at')->get();

        return response()->json([
            'data' => $formatter->testimonials($testimonials),
        ]);
    }

    public function publish(ConfirmModerationRequest $request, Testimonial $testimonial, ModerateAiTestimonial $action, AiDataFormatter $formatter): JsonResponse
    {
        $testimonial = $action->publish($testimonial);

        return response()->json([
            'data' => $formatter->testimonial($testimonial),
        ]);
    }

    public function reject(ConfirmModerationRequest $request, Testimonial $testimonial, ModerateAiTestimonial $action): JsonResponse
    {
        $action->reject($testimonial);

        return response()->json([
            'message' => 'Das Testimonial wurde abgelehnt.',
        ]);
    }
}
