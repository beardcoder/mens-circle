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
use PhpStaticAnalysis\Attributes\Returns;

class EventRegistrationConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event
    ) {
        //
    }

    public function envelope(): Envelope
    {
        $participant = $this->registration->participant;

        return new Envelope(
            to: [new Address($participant->email, $participant->fullName)],
            subject: 'Anmeldebestätigung: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.event-registration',
        );
    }

    #[Returns('array<int, \Illuminate\Mail\Mailables\Attachment>')]
    public function attachments(): array
    {
        $icalContent = $this->event->generateICalContent();
        $filename = 'event-' . $this->event->slug . '.ics';

        return [
            Attachment::fromData(fn (): string => $icalContent, $filename)
                ->withMime('text/calendar'),
        ];
    }
}
