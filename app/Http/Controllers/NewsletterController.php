<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriptionRequest;
use App\Models\NewsletterSubscription;
use App\Models\Participant;
use App\Notifications\NewsletterSubscribed;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use RuntimeException;
use SensitiveParameter;

final class NewsletterController
{
    public function subscribe(NewsletterSubscriptionRequest $request): JsonResponse
    {
        /** @var string $email */
        $email = $request->validated()['email'];

        try {
            $participant = Participant::findOrCreateByEmail($email);

            $subscription = NewsletterSubscription::withTrashed()->where('participant_id', $participant->id)->first();

            if ($subscription?->isActive()) {
                throw new RuntimeException('Diese E-Mail-Adresse ist bereits für den Newsletter angemeldet.');
            }

            if ($subscription) {
                $subscription->restore();
                $subscription->resubscribe();
            } else {
                $subscription = NewsletterSubscription::create([
                    'participant_id' => $participant->id,
                ]);
            }

            $participant->notify(new NewsletterSubscribed($subscription));

            return response()->json([
                'success' => true,
                'message' => 'Vielen Dank! Du wurdest erfolgreich für den Newsletter angemeldet.',
            ]);
        } catch (RuntimeException $runtimeException) {
            return response()->json([
                'success' => false,
                'message' => $runtimeException->getMessage(),
            ], 409);
        }
    }

    public function unsubscribe(#[SensitiveParameter] string $token): View
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
