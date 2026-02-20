<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistPromotion extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Registration $registration,
        public readonly Event $event,
    ) {}

    public function envelope(): Envelope
    {
        $participant = $this->registration->participant;

        return new Envelope(
            to: [new Address($participant->email, $participant->fullName)],
            subject: 'Ein Platz ist frei – ' . $this->event->title,
        );
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
            Attachment::fromData(fn (): string => $icalContent, $filename)
                ->withMime('text/calendar'),
        ];
    }
}
