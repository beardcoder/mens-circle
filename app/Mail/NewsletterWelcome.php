<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcome extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly NewsletterSubscription $subscription,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Willkommen beim Männerkreis Niederbayern/ Straubing Newsletter');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.newsletter-welcome');
    }
}
