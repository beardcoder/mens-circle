<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminEventRegistrationNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Registration $registration,
        public readonly Event $event,
    ) {}

    public function envelope(): Envelope
    {
        /** @var string $adminEmail */
        $adminEmail = config('mail.admin.address', 'hallo@mens-circle.de');
        /** @var string $adminName */
        $adminName = config('mail.admin.name', 'Männerkreis Admin');

        return new Envelope(to: [new Address($adminEmail, $adminName)], subject: 'Neue Anmeldung: ' . $this->event->title);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.admin-event-registration', with: [
            'registrationCount' => $this->event->activeRegistrations()->count(),
        ]);
    }
}
