<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Newsletter $newsletter
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update status to sending
        $this->newsletter->update(['status' => 'sending']);

        $recipientCount = 0;
        $failedRecipients = [];

        // Get all active subscribers and send emails in chunks
        NewsletterSubscription::query()
            ->where('status', 'active')
            ->chunk(100, function ($subscriptions) use (&$recipientCount, &$failedRecipients): void {
                foreach ($subscriptions as $subscription) {
                    try {
                        Mail::to($subscription->email)
                            ->send(new NewsletterMail($this->newsletter, $subscription));

                        $recipientCount++;
                    } catch (\Exception $e) {
                        \Log::error('Failed to send newsletter to subscriber', [
                            'newsletter_id' => $this->newsletter->id,
                            'subscription_id' => $subscription->id,
                            'email' => $subscription->email,
                            'error' => $e->getMessage(),
                        ]);
                        $failedRecipients[] = $subscription->email;
                    }
                }
            });

        // Update newsletter as sent
        $this->newsletter->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_count' => $recipientCount,
        ]);

        // Log summary if there were failures
        if ($failedRecipients !== []) {
            \Log::warning('Newsletter sending completed with failures', [
                'newsletter_id' => $this->newsletter->id,
                'successful' => $recipientCount,
                'failed' => count($failedRecipients),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Newsletter job failed completely', [
            'newsletter_id' => $this->newsletter->id,
            'error' => $exception->getMessage(),
        ]);

        // Reset status to draft so it can be retried manually
        $this->newsletter->update(['status' => 'draft']);
    }
}
