<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum NavigationLocation: string implements HasLabel
{
    case Header = 'header';
    case FooterPrimary = 'footer_primary';
    case FooterContact = 'footer_contact';
    case FooterLegal = 'footer_legal';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Header => 'Header',
            self::FooterPrimary => 'Footer – Navigation',
            self::FooterContact => 'Footer – Kontakt',
            self::FooterLegal => 'Footer – Rechtliches',
        };
    }

    public function umamiEventName(): string
    {
        return match ($this) {
            self::Header => 'nav-click',
            self::FooterPrimary, self::FooterContact, self::FooterLegal => 'footer-link',
        };
    }
}
