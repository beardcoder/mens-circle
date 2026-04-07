<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\EventReminder as EventReminderMail;
use App\Models\Event;
use App\Models\Registration;
use App\Notifications\Channels\SevenIoChannel;
use App\Notifications\Messages\SevenIoMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

final class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Registration $registration,
        private readonly Event $event,
        private readonly bool $isToday = false,
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
        return (new EventReminderMail($this->registration, $this->event, $this->isToday))
            ->to($notifiable->routeNotificationFor('mail', $this)); // @phpstan-ignore method.notFound
    }

    public function toSevenIo(object $notifiable): SevenIoMessage
    {
        $timeWord = $this->isToday ? 'heute' : 'morgen';
        $eventTime = $this->event->start_time->format('H:i');

        return SevenIoMessage::create(
            "Hallo {$this->registration->participant->first_name}, {$timeWord} ist Maennerkreis! {$this->event->title} um {$eventTime} Uhr in {$this->event->location}. Bis bald!",
        );
    }
}
