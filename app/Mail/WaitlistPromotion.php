<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WaitlistPromotion extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Registration $registration,
        public readonly Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ein Platz ist frei – ' . $this->event->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.waitlist-promotion');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $icalContent = $this->event->generateICalContent();
        $filename = "event-{$this->event->slug}.ics";

        return [
            Attachment::fromData(static fn(): string => $icalContent, $filename)->withMime('text/calendar'),
        ];
    }
}
