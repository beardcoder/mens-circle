<?php

declare(strict_types=1);

namespace App\Features\Newsletters\Http\Controllers;

use App\Features\Newsletters\Domain\Services\NewsletterSubscriptionService;
use App\Features\Newsletters\Http\Requests\NewsletterSubscriptionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterSubscriptionService $subscriptionService
    ) {
    }

    public function subscribe(NewsletterSubscriptionRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        try {
            $this->subscriptionService->subscribe($email);

            return response()->json([
                'success' => true,
                'message' => 'Vielen Dank! Du wurdest erfolgreich für den Newsletter angemeldet.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function unsubscribe(string $token): View
    {
        try {
            $this->subscriptionService->unsubscribe($token);

            return view('newsletter.unsubscribed', [
                'message' => 'Du wurdest erfolgreich vom Newsletter abgemeldet.',
            ]);
        } catch (\RuntimeException $e) {
            return view('newsletter.unsubscribed', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
