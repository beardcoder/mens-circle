<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SubscribeToNewsletterAction;
use App\Http\Requests\NewsletterSubscriptionRequest;
use App\Models\NewsletterSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscriptionRequest $request, SubscribeToNewsletterAction $action): JsonResponse
    {
        $email = $request->validated()['email'];

        try {
            $action->execute($email);

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
        $subscription = NewsletterSubscription::where('token', $token)->firstOrFail();

        if (!$subscription->isActive()) {
            return view('newsletter.unsubscribed', [
                'message' => 'Diese E-Mail-Adresse wurde bereits vom Newsletter abgemeldet.',
            ]);
        }

        $subscription->unsubscribe();

        return view('newsletter.unsubscribed', [
            'message' => 'Du wurdest erfolgreich vom Newsletter abgemeldet.',
        ]);
    }
}
