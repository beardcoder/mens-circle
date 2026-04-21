<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\GenerateAiNewsletterDraft;
use App\Actions\Ai\SendAiNewsletter;
use App\Http\Requests\Ai\GenerateNewsletterRequest;
use App\Http\Requests\Ai\SendNewsletterRequest;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use App\Services\Ai\AiDataFormatter;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class NewsletterManagementController
{
    public function generate(GenerateNewsletterRequest $request, GenerateAiNewsletterDraft $action, AiDataFormatter $formatter): JsonResponse
    {
        $newsletter = $action->execute($request->validated());

        return response()->json([
            'data' => $formatter->newsletter($newsletter),
        ], 201);
    }

    public function preview(Newsletter $newsletter, AiDataFormatter $formatter): JsonResponse
    {
        return response()->json([
            'data' => $formatter->newsletter($newsletter),
            'recipient_count' => NewsletterSubscription::activeCount(),
            'can_send' => ! $newsletter->isSent(),
        ]);
    }

    public function send(SendNewsletterRequest $request, Newsletter $newsletter, SendAiNewsletter $action, AiDataFormatter $formatter): JsonResponse
    {
        try {
            $newsletter = $action->execute($newsletter);
        } catch (RuntimeException $runtimeException) {
            return response()->json([
                'message' => $runtimeException->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => $formatter->newsletter($newsletter),
            'message' => 'Der Newsletter wird im Hintergrund versendet.',
        ]);
    }
}
