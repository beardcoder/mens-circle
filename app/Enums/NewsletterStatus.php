<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumOptions;

enum NewsletterStatus: string
{
    use HasEnumOptions;
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
}
