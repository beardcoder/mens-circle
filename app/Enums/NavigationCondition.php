<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum NavigationCondition: string implements HasLabel
{
    case NextEvent = 'next_event';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) { self::NextEvent => 'Nur anzeigen, wenn ein nächster Termin existiert' };
    }
}
