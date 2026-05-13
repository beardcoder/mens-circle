<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\EventRegistrationConfirmation as EventRegistrationConfirmationMail;
use App\Models\Event;
use App\Models\Registration;
use App\Notifications\Channels\SevenIoChannel;
use App\Notifications\Messages\SevenIoMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

final class EventRegistrationConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Registration $registration,
        private readonly Event $event,
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable->routeNotificationFor('sevenIo', $this)) { // @phpstan-ignore method.notFound
            $channels[] = SevenIoChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new EventRegistrationConfirmationMail($this->registration, $this->event))
            ->to($notifiable->routeNotificationFor('mail', $this)); // @phpstan-ignore method.notFound
    }

    public function toSevenIo(object $notifiable): SevenIoMessage
    {
        $eventDate = $this->event->event_date->format('d.m.Y');
        $eventTime = $this->event->start_time->format('H:i');

        return SevenIoMessage::create(
            "Hallo {$this->registration->participant->first_name}! Deine Anmeldung fuer den Maennerkreis am {$eventDate} um {$eventTime} Uhr ist bestaetigt. Wir freuen uns auf dich!",
        );
    }
}
