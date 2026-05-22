<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationCondition: string implements HasLabel
{
    case NextEvent = 'next_event';

    public function getLabel(): string
    {
        return match ($this) {
            self::NextEvent => 'Nur anzeigen, wenn ein nächster Termin existiert',
        };
    }
}
