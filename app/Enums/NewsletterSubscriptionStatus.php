<?php

declare(strict_types=1);

namespace App\Enums;

enum NewsletterSubscriptionStatus: string
{
    case Active = 'active';
    case Unsubscribed = 'unsubscribed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Aktiv',
            self::Unsubscribed => 'Abgemeldet',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Unsubscribed => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->toArray();
    }
}
