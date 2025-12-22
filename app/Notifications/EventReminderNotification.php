<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public EventRegistration $registration,
        public Event $event
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Für später: SMS-Channel hinzufügen wenn gewünscht
        // return ['mail', 'vonage'];
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Erinnerung: '.$this->event->title.' ist morgen!')
            ->markdown('emails.event-reminder', [
                'registration' => $this->registration,
                'event' => $this->event,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_date' => $this->event->event_date,
        ];
    }

    /**
     * SMS notification (für zukünftige Verwendung mit Vonage/Nexmo).
     *
     * Uncomment when SMS functionality is needed:
     *
     * public function toVonage(object $notifiable): VonageMessage
     * {
     *     return (new VonageMessage)
     *         ->content('Erinnerung: ' . $this->event->title . ' findet morgen um ' .
     *                   $this->event->start_time->format('H:i') . ' Uhr statt. Ort: ' .
     *                   $this->event->location);
     * }
     */
}
