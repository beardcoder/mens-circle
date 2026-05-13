<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\AdminEventRegistrationNotification;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pushover\PushoverChannel;
use NotificationChannels\Pushover\PushoverMessage;

final class EventRegistrationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Registration $registration,
        private readonly Event $event,
        private readonly Participant $participant,
        private readonly bool $isWaitlist = false,
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (config('services.pushover.token') && config('services.pushover.user_key')) {
            $channels[] = PushoverChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new AdminEventRegistrationNotification($this->registration, $this->event))
            ->to($notifiable->routeNotificationFor('mail', $this)); // @phpstan-ignore method.notFound
    }

    public function toPushover(object $notifiable): PushoverMessage
    {
        $name = "{$this->participant->first_name} {$this->participant->last_name}";

        if ($this->isWaitlist) {
            return PushoverMessage::create("<b>{$name}</b> hat sich auf die <b>Warteliste</b> für <b>{$this->event->title}</b> eingetragen.")
                ->title('Neue Wartelisten-Anmeldung')
                ->url(route('event.show.slug', $this->event->slug), $this->event->title)
                ->html();
        }

        $eventDate = $this->event->event_date->format('d.m.Y');
        $eventTime = $this->event->start_time->format('H:i');

        return PushoverMessage::create("<b>{$name}</b> hat sich für <b>{$this->event->title}</b> am {$eventDate} um {$eventTime} Uhr angemeldet.")
            ->title('Neue Event-Anmeldung')
            ->url(route('event.show.slug', $this->event->slug), $this->event->title)
            ->html();
    }
}
