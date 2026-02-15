<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Enum;

enum NotificationChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
}
