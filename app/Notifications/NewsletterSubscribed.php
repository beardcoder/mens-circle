<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\NewsletterWelcome as NewsletterWelcomeMail;
use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

final class NewsletterSubscribed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly NewsletterSubscription $subscription,
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
        return (new NewsletterWelcomeMail($this->subscription))
            ->to($notifiable->routeNotificationFor('mail', $this)); // @phpstan-ignore method.notFound
    }
}
