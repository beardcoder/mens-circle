<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\WaitlistPromotion as WaitlistPromotionMail;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

final class WaitlistParticipantPromoted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Registration $registration,
        private readonly Event $event,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new WaitlistPromotionMail($this->registration, $this->event))
            ->to($notifiable->routeNotificationFor('mail', $this)); // @phpstan-ignore method.notFound
    }
}
