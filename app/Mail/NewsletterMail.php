<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Newsletter $newsletter,
        public readonly NewsletterSubscription $subscription,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->newsletter->subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.newsletter');
    }
}
