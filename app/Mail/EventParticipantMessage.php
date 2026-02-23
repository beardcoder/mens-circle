<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventParticipantMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $mailSubject,
        public readonly string $mailContent,
        public readonly Event $event,
        public readonly ?string $participantName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        $processedContent = $this->participantName !== null
            ? str_replace('{first_name}', e($this->participantName), $this->mailContent)
            : $this->mailContent;

        return new Content(
            markdown: 'emails.event-participant-message',
            with: [
                'mailContent' => $processedContent,
                'event' => $this->event,
                'participantName' => $this->participantName,
            ],
        );
    }
}
