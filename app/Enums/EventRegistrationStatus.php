<?php

declare(strict_types=1);

namespace App\Enums;

enum EventRegistrationStatus: string
{
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Waitlist = 'waitlist';

    public function getLabel(): string
    {
        return match ($this) {
            self::Confirmed => 'Bestätigt',
            self::Cancelled => 'Abgesagt',
            self::Waitlist => 'Warteliste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Confirmed => 'success',
            self::Cancelled => 'danger',
            self::Waitlist => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->toArray();
    }
}
