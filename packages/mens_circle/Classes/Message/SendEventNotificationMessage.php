<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Message;

use BeardCoder\MensCircle\Domain\Enum\NotificationChannel;
use BeardCoder\MensCircle\Domain\Enum\NotificationType;

final readonly class SendEventNotificationMessage
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public int $registrationUid,
        public NotificationType $type,
        public NotificationChannel $channel,
        public array $settings = [],
    ) {}
}
