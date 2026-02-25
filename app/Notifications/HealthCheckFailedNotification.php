<?php

declare(strict_types=1);

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Result;

class HealthCheckFailedNotification extends Notification
{
    use Queueable;

    /**
     * @param array<Result> $results
     */
    public function __construct(
        public readonly array $results,
    ) {}

    /**
     * Determine if the notification should be sent based on throttle settings.
     *
     * Note: This method updates the cache timestamp when returning true. In rare cases where
     * notification delivery fails after this method returns true, the throttle window will
     * still be enforced. This is an acceptable trade-off for health check notifications.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (!config('health.notifications.enabled')) {
            return false;
        }

        $throttleMinutes = config('health.notifications.throttle_notifications_for_minutes', 60);
        \assert(\is_int($throttleMinutes), 'health.notifications.throttle_notifications_for_minutes must be an integer');

        if ($throttleMinutes <= 0) {
            return true;
        }

        /** @var string $throttleKey */
        $throttleKey = config('health.notifications.throttle_notifications_key', 'health:latestNotificationSentAt:');
        $cacheKey = $throttleKey . $channel;
        $lockKey = $cacheKey . ':lock';

        // Use atomic lock to prevent race conditions
        $lock = Cache::lock($lockKey, 10);

        try {
            if ($lock->get()) {
                /** @var Carbon|null $lastNotificationSentAt */
                $lastNotificationSentAt = Cache::get($cacheKey);

                if ($lastNotificationSentAt === null || $lastNotificationSentAt->diffInMinutes(now()) >= $throttleMinutes) {
                    Cache::put($cacheKey, now(), $throttleMinutes * 60);

                    return true;
                }

                return false;
            }

            // If we couldn't acquire the lock, assume another process is sending
            return false;
        } finally {
            $lock->release();
        }
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
        $failedChecks = collect($this->results)->filter(static fn(Result $result): bool => $result->status->value === 'failed');

        $warningChecks = collect($this->results)->filter(static fn(Result $result): bool => $result->status->value === 'warning');

        /** @var string $appName */
        $appName = config('app.name', 'Application');

        $message = new MailMessage()
            ->subject('Health Check Warnung - ' . $appName)
            ->greeting('Health Check Benachrichtigung')
            ->line('Es wurden Probleme bei den automatischen Health Checks festgestellt:');

        if ($failedChecks->isNotEmpty()) {
            $message->line('**Fehlgeschlagene Checks:**');
            foreach ($failedChecks as $result) {
                $message->line(\sprintf('- %s: %s', $result->check->getName(), $result->notificationMessage));
            }
        }

        if ($warningChecks->isNotEmpty()) {
            $message->line('**Warnungen:**');
            foreach ($warningChecks as $result) {
                $message->line(\sprintf('- %s: %s', $result->check->getName(), $result->notificationMessage));
            }
        }

        return $message
            ->line('Bitte überprüfe den Server-Status und behebe die Probleme.')
            ->action('Health Status anzeigen', url('/admin/health'))
            ->line('Diese Benachrichtigung wurde automatisch versendet.')
            ->salutation('Dein ' . $appName . ' System');
    }
}
