<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public EventRegistration $registration,
        public Event $event
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address($this->registration->email, $this->registration->first_name.' '.$this->registration->last_name)],
            subject: 'Erinnerung: '.$this->event->title.' ist morgen!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.event-reminder',
        );
    }
}
