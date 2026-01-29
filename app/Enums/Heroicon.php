<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumOptions;

enum Heroicon: string
{
    use HasEnumOptions;

    // Communication & Social
    case Envelope = 'envelope';
    case Phone = 'phone';
    case ChatBubbleLeftRight = 'chat-bubble-left-right';
    case AtSymbol = 'at-symbol';

    // Social Media & Web
    case GlobeAlt = 'globe-alt';
    case Link = 'link';
    case Share = 'share';

    // Location & Navigation
    case MapPin = 'map-pin';
    case Map = 'map';
    case Home = 'home';
    case BuildingOffice = 'building-office';

    // Users & People
    case User = 'user';
    case UserGroup = 'user-group';
    case Users = 'users';
    case Heart = 'heart';

    // Calendar & Time
    case Calendar = 'calendar';
    case CalendarDays = 'calendar-days';
    case Clock = 'clock';

    // Information & Support
    case InformationCircle = 'information-circle';
    case QuestionMarkCircle = 'question-mark-circle';
    case ExclamationCircle = 'exclamation-circle';
    case LightBulb = 'light-bulb';

    // Actions
    case ArrowRight = 'arrow-right';
    case ArrowLeft = 'arrow-left';
    case ArrowUp = 'arrow-up';
    case ArrowDown = 'arrow-down';
    case ChevronRight = 'chevron-right';
    case ChevronLeft = 'chevron-left';
    case ChevronUp = 'chevron-up';
    case ChevronDown = 'chevron-down';
    case Plus = 'plus';
    case Minus = 'minus';
    case XMark = 'x-mark';
    case Check = 'check';
    case CheckCircle = 'check-circle';

    // Media & Content
    case Photo = 'photo';
    case Camera = 'camera';
    case VideoCamera = 'video-camera';
    case Document = 'document';
    case Newspaper = 'newspaper';
    case BookOpen = 'book-open';

    // Commerce & Business
    case ShoppingBag = 'shopping-bag';
    case ShoppingCart = 'shopping-cart';
    case CreditCard = 'credit-card';
    case CurrencyEuro = 'currency-euro';
    case Banknotes = 'banknotes';

    // Settings & Tools
    case Cog6Tooth = 'cog-6-tooth';
    case Wrench = 'wrench';
    case AdjustmentsHorizontal = 'adjustments-horizontal';

    // Security
    case LockClosed = 'lock-closed';
    case LockOpen = 'lock-open';
    case ShieldCheck = 'shield-check';
    case Key = 'key';

    // Other
    case Star = 'star';
    case Sparkles = 'sparkles';
    case Fire = 'fire';
    case Megaphone = 'megaphone';
    case Bell = 'bell';
    case Flag = 'flag';
    case Tag = 'tag';
    case Ticket = 'ticket';

    public function getLabel(): string
    {
        return match ($this) {
            // Communication & Social
            self::Envelope => 'E-Mail (Umschlag)',
            self::Phone => 'Telefon',
            self::ChatBubbleLeftRight => 'Chat',
            self::AtSymbol => '@-Symbol',

            // Social Media & Web
            self::GlobeAlt => 'Weltkugel',
            self::Link => 'Link',
            self::Share => 'Teilen',

            // Location & Navigation
            self::MapPin => 'Standort-Pin',
            self::Map => 'Karte',
            self::Home => 'Haus',
            self::BuildingOffice => 'Gebäude',

            // Users & People
            self::User => 'Benutzer',
            self::UserGroup => 'Benutzergruppe',
            self::Users => 'Benutzer (Mehrere)',
            self::Heart => 'Herz',

            // Calendar & Time
            self::Calendar => 'Kalender',
            self::CalendarDays => 'Kalender (Tage)',
            self::Clock => 'Uhr',

            // Information & Support
            self::InformationCircle => 'Information',
            self::QuestionMarkCircle => 'Frage',
            self::ExclamationCircle => 'Warnung',
            self::LightBulb => 'Glühbirne',

            // Actions
            self::ArrowRight => 'Pfeil Rechts',
            self::ArrowLeft => 'Pfeil Links',
            self::ArrowUp => 'Pfeil Oben',
            self::ArrowDown => 'Pfeil Unten',
            self::ChevronRight => 'Chevron Rechts',
            self::ChevronLeft => 'Chevron Links',
            self::ChevronUp => 'Chevron Oben',
            self::ChevronDown => 'Chevron Unten',
            self::Plus => 'Plus',
            self::Minus => 'Minus',
            self::XMark => 'X',
            self::Check => 'Häkchen',
            self::CheckCircle => 'Häkchen (Kreis)',

            // Media & Content
            self::Photo => 'Foto',
            self::Camera => 'Kamera',
            self::VideoCamera => 'Videokamera',
            self::Document => 'Dokument',
            self::Newspaper => 'Zeitung',
            self::BookOpen => 'Buch (Offen)',

            // Commerce & Business
            self::ShoppingBag => 'Einkaufstasche',
            self::ShoppingCart => 'Einkaufswagen',
            self::CreditCard => 'Kreditkarte',
            self::CurrencyEuro => 'Euro',
            self::Banknotes => 'Geldscheine',

            // Settings & Tools
            self::Cog6Tooth => 'Zahnrad',
            self::Wrench => 'Schraubenschlüssel',
            self::AdjustmentsHorizontal => 'Einstellungen',

            // Security
            self::LockClosed => 'Schloss (Geschlossen)',
            self::LockOpen => 'Schloss (Offen)',
            self::ShieldCheck => 'Schild (Häkchen)',
            self::Key => 'Schlüssel',

            // Other
            self::Star => 'Stern',
            self::Sparkles => 'Funken',
            self::Fire => 'Feuer',
            self::Megaphone => 'Megaphon',
            self::Bell => 'Glocke',
            self::Flag => 'Flagge',
            self::Tag => 'Tag',
            self::Ticket => 'Ticket',
        };
    }

    public function getName(): string
    {
        return $this->value;
    }

    public static function fromName(string $name): ?self
    {
        return self::tryFrom($name);
    }
}
