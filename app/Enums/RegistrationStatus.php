<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumOptions;

enum RegistrationStatus: string
{
    use HasEnumOptions;
    case Registered = 'registered';
    case Waitlist = 'waitlist';
    case Cancelled = 'cancelled';
    case Attended = 'attended';

    public function getLabel(): string
    {
        return match ($this) {
            self::Registered => 'Angemeldet',
            self::Waitlist => 'Warteliste',
            self::Cancelled => 'Abgesagt',
            self::Attended => 'Teilgenommen',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Registered => 'info',
            self::Waitlist => 'warning',
            self::Cancelled => 'danger',
            self::Attended => 'success',
        };
    }
}
