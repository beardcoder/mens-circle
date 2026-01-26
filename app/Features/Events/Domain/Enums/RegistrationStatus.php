<?php

declare(strict_types=1);

namespace App\Features\Events\Domain\Enums;

enum RegistrationStatus: string
{
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

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->toArray();
    }
}
