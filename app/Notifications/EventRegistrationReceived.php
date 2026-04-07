<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Pushover\PushoverChannel;
use NotificationChannels\Pushover\PushoverMessage;

final class EventRegistrationReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Event $event,
        private readonly Participant $participant,
        private readonly bool $isWaitlist = false,
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [PushoverChannel::class];
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
