<?php

declare(strict_types=1);

namespace App\Enums;

enum NewsletterStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Entwurf',
            self::Sending => 'Wird gesendet',
            self::Sent => 'Gesendet',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sending => 'warning',
            self::Sent => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [
$status->value => $status->getLabel()
])
            ->toArray();
    }
}
