<?php

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

        // Get all active subscribers and send emails in chunks
        NewsletterSubscription::query()
            ->where('status', 'active')
            ->chunk(100, function ($subscriptions) use (&$recipientCount) {
                foreach ($subscriptions as $subscription) {
                    Mail::to($subscription->email)
                        ->send(new NewsletterMail($this->newsletter, $subscription));

                    $recipientCount++;
                }
            });

        // Update newsletter as sent
        $this->newsletter->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_count' => $recipientCount,
        ]);
    }
}
