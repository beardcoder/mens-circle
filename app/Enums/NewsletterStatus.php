<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum NewsletterStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';

    #[\Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Entwurf',
            self::Sending => 'Wird gesendet',
            self::Sent => 'Gesendet',
        };
    }

    #[\Override]
    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sending => 'warning',
            self::Sent => 'success',
        };
    }
}
