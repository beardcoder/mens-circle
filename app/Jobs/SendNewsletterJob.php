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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Newsletter $newsletter
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update status to sending
        $this->newsletter->update(['status' => NewsletterStatus::Sending]);

        $recipientCount = 0;
        $failedRecipients = [];

        // Get all active subscribers and send emails in chunks
        NewsletterSubscription::query()
            ->where('status', 'active')
            ->with('participant')
            ->chunk(100, function (\Illuminate\Support\Collection $subscriptions) use (&$recipientCount, &$failedRecipients): void {
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
                            'email' => $subscription->participant->email ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                        $failedRecipients[] = $subscription->participant->email ?? 'unknown';
                    }
                }
            });

        // Update newsletter as sent
        $this->newsletter->update([
            'status' => NewsletterStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $recipientCount,
        ]);

        // Log summary if there were failures
        if ($failedRecipients !== []) {
            Log::warning('Newsletter sending completed with failures', [
                'newsletter_id' => $this->newsletter->id,
                'successful' => $recipientCount,
                'failed' => count($failedRecipients),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Newsletter job failed completely', [
            'newsletter_id' => $this->newsletter->id,
            'error' => $exception->getMessage(),
        ]);

        // Reset status to draft so it can be retried manually
        $this->newsletter->update(['status' => NewsletterStatus::Draft]);
    }
}
