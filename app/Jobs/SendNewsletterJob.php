<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\NewsletterStatus;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Newsletter $newsletter,
    ) {}

    public function handle(): void
    {
        $this->newsletter->update([
            'status' => NewsletterStatus::Sending,
        ]);

        $recipientCount = 0;
        $failedCount = 0;

        NewsletterSubscription::query()
            ->whereNull('unsubscribed_at')
            ->with('participant')
            ->chunk(100, function (Collection $subscriptions) use (&$recipientCount, &$failedCount): void {
                /** @var NewsletterSubscription $subscription */
                foreach ($subscriptions as $subscription) {
                    try {
                        Mail::to($subscription->participant->email)
                            ->send(new NewsletterMail($this->newsletter, $subscription));

                        $recipientCount++;
                    } catch (Exception $e) {
                        Log::error('Failed to send newsletter to subscriber', [
                            'newsletter_id' => $this->newsletter->id,
                            'subscription_id' => $subscription->id,
                            'error' => $e->getMessage(),
                        ]);
                        $failedCount++;
                    }
                }
            });

        $this->newsletter->update([
            'status' => NewsletterStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $recipientCount,
        ]);

        if ($failedCount > 0) {
            Log::warning('Newsletter sending completed with failures', [
                'newsletter_id' => $this->newsletter->id,
                'successful' => $recipientCount,
                'failed' => $failedCount,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Newsletter job failed completely', [
            'newsletter_id' => $this->newsletter->id,
            'error' => $exception->getMessage(),
        ]);

        // Reset status to draft so it can be retried manually
        $this->newsletter->update([
            'status' => NewsletterStatus::Draft,
        ]);
    }
}
