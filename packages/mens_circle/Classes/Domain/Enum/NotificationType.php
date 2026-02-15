<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Enum;

enum NotificationType: string
{
    case RegistrationConfirmation = 'registration_confirmation';
    case Reminder = 'reminder';
}
