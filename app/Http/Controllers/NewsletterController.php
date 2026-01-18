<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriptionRequest;
use App\Mail\NewsletterWelcome;
use App\Models\NewsletterSubscription;
use App\Models\Participant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscriptionRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        // Find or create participant
        $participant = Participant::findOrCreateByEmail($email);

        // Check for existing subscription
        $subscription = NewsletterSubscription::withTrashed()
            ->where('participant_id', $participant->id)
            ->first();

        if ($subscription?->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Diese E-Mail-Adresse ist bereits für den Newsletter angemeldet.',
            ], 409);
        }

        if ($subscription) {
            $subscription->restore();
            $subscription->resubscribe();
        } else {
            $subscription = NewsletterSubscription::create([
                'participant_id' => $participant->id,
            ]);
        }

        try {
            Mail::to($participant->email)->queue(new NewsletterWelcome($subscription));
        } catch (Exception $exception) {
            Log::error('Failed to send newsletter welcome email', [
                'subscription_id' => $subscription->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vielen Dank! Du wurdest erfolgreich für den Newsletter angemeldet.',
        ]);
    }

    public function unsubscribe(string $token): View
    {
        $subscription = NewsletterSubscription::where('token', $token)->firstOrFail();

        if (! $subscription->isActive()) {
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
