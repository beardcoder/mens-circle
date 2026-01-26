<?php

declare(strict_types=1);

namespace App\Features\Newsletters\Domain\Services;

use App\Domain\Models\Participant;
use App\Features\Newsletters\Domain\Models\NewsletterSubscription;
use App\Mail\NewsletterWelcome;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final readonly class NewsletterSubscriptionService
{
    /**
     * Subscribe a participant to the newsletter.
     *
     * @throws \RuntimeException
     */
    public function subscribe(string $email): NewsletterSubscription
    {
        // Find or create participant
        $participant = Participant::findOrCreateByEmail($email);

        // Check for existing subscription
        $subscription = NewsletterSubscription::withTrashed()
            ->where('participant_id', $participant->id)
            ->first();

        if ($subscription?->isActive()) {
            throw new \RuntimeException('Diese E-Mail-Adresse ist bereits für den Newsletter angemeldet.');
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
        $this->sendWelcomeEmail($subscription);

        return $subscription;
    }

    /**
     * Unsubscribe a participant from the newsletter.
     */
    public function unsubscribe(string $token): NewsletterSubscription
    {
        $subscription = NewsletterSubscription::where('token', $token)->firstOrFail();

        if (! $subscription->isActive()) {
            throw new \RuntimeException('Diese E-Mail-Adresse wurde bereits vom Newsletter abgemeldet.');
        }

        $subscription->unsubscribe();

        return $subscription;
    }

    /**
     * Send welcome email to new subscriber.
     */
    private function sendWelcomeEmail(NewsletterSubscription $subscription): void
    {
        try {
            Mail::to($subscription->participant->email)->queue(new NewsletterWelcome($subscription));
        } catch (Exception $exception) {
            Log::error('Failed to send newsletter welcome email', [
                'subscription_id' => $subscription->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
