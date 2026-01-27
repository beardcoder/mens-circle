<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\NewsletterWelcome;
use App\Models\NewsletterSubscription;
use App\Models\Participant;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SubscribeToNewsletterAction
{
    public function execute(string $email): NewsletterSubscription
    {
        // Find or create participant
        $participant = Participant::findOrCreateByEmail($email);

        // Check for existing subscription
        $subscription = NewsletterSubscription::withTrashed()
            ->where('participant_id', $participant->id)
            ->first();

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

        // Send welcome email
        try {
            Mail::to($participant->email)->queue(new NewsletterWelcome($subscription));
        } catch (Exception $exception) {
            Log::error('Failed to send newsletter welcome email', [
                'subscription_id' => $subscription->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $subscription;
    }
}
