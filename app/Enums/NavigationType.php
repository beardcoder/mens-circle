<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumOptions;
use Filament\Support\Contracts\HasLabel;

enum NavigationType: string implements HasLabel
{
    use HasEnumOptions;

    case Header = 'header';
    case Footer = 'footer';
    case Legal = 'legal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Header => 'Hauptnavigation (Header)',
            self::Footer => 'Footer Navigation',
            self::Legal => 'Rechtliches',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Header => 'Navigation im Header, inkl. mobiles Menü',
            self::Footer => 'Links im Footer-Bereich',
            self::Legal => 'Impressum, Datenschutz, etc.',
        };
    }
}
