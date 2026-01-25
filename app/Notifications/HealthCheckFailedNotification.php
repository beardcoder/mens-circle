<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Health\Checks\Result;

class HealthCheckFailedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<Result>  $results
     */
    public function __construct(
        public array $results
    ) {
    }

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $failedChecks = collect($this->results)
            ->filter(fn (Result $result) => $result->status->value === 'failed');

        $warningChecks = collect($this->results)
            ->filter(fn (Result $result) => $result->status->value === 'warning');

        $message = (new MailMessage())
            ->subject('Health Check Warnung - ' . config('app.name'))
            ->greeting('Health Check Benachrichtigung')
            ->line('Es wurden Probleme bei den automatischen Health Checks festgestellt:');

        if ($failedChecks->isNotEmpty()) {
            $message->line('**Fehlgeschlagene Checks:**');
            foreach ($failedChecks as $result) {
                $message->line("- {$result->check->getName()}: {$result->notificationMessage}");
            }
        }

        if ($warningChecks->isNotEmpty()) {
            $message->line('**Warnungen:**');
            foreach ($warningChecks as $result) {
                $message->line("- {$result->check->getName()}: {$result->notificationMessage}");
            }
        }

        return $message
            ->line('Bitte überprüfe den Server-Status und behebe die Probleme.')
            ->action('Health Status anzeigen', url('/health'))
            ->line('Diese Benachrichtigung wurde automatisch versendet.')
            ->salutation('Dein ' . config('app.name') . ' System');
    }
}
