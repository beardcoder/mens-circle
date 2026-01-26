<?php

declare(strict_types=1);

namespace App\Enums;

enum Heroicon: string
{
    // Communication & Social
    case ENVELOPE = 'envelope';
    case PHONE = 'phone';
    case CHAT_BUBBLE_LEFT_RIGHT = 'chat-bubble-left-right';
    case AT_SYMBOL = 'at-symbol';

    // Social Media & Web
    case GLOBE_ALT = 'globe-alt';
    case LINK = 'link';
    case SHARE = 'share';

    // Location & Navigation
    case MAP_PIN = 'map-pin';
    case MAP = 'map';
    case HOME = 'home';
    case BUILDING_OFFICE = 'building-office';

    // Users & People
    case USER = 'user';
    case USER_GROUP = 'user-group';
    case USERS = 'users';
    case HEART = 'heart';

    // Calendar & Time
    case CALENDAR = 'calendar';
    case CALENDAR_DAYS = 'calendar-days';
    case CLOCK = 'clock';

    // Information & Support
    case INFORMATION_CIRCLE = 'information-circle';
    case QUESTION_MARK_CIRCLE = 'question-mark-circle';
    case EXCLAMATION_CIRCLE = 'exclamation-circle';
    case LIGHT_BULB = 'light-bulb';

    // Actions
    case ARROW_RIGHT = 'arrow-right';
    case ARROW_LEFT = 'arrow-left';
    case ARROW_UP = 'arrow-up';
    case ARROW_DOWN = 'arrow-down';
    case CHEVRON_RIGHT = 'chevron-right';
    case CHEVRON_LEFT = 'chevron-left';
    case CHEVRON_UP = 'chevron-up';
    case CHEVRON_DOWN = 'chevron-down';
    case PLUS = 'plus';
    case MINUS = 'minus';
    case X_MARK = 'x-mark';
    case CHECK = 'check';
    case CHECK_CIRCLE = 'check-circle';

    // Media & Content
    case PHOTO = 'photo';
    case CAMERA = 'camera';
    case VIDEO_CAMERA = 'video-camera';
    case DOCUMENT = 'document';
    case NEWSPAPER = 'newspaper';
    case BOOK_OPEN = 'book-open';

    // Commerce & Business
    case SHOPPING_BAG = 'shopping-bag';
    case SHOPPING_CART = 'shopping-cart';
    case CREDIT_CARD = 'credit-card';
    case CURRENCY_EURO = 'currency-euro';
    case BANKNOTES = 'banknotes';

    // Settings & Tools
    case COG_6_TOOTH = 'cog-6-tooth';
    case WRENCH = 'wrench';
    case ADJUSTMENTS_HORIZONTAL = 'adjustments-horizontal';

    // Security
    case LOCK_CLOSED = 'lock-closed';
    case LOCK_OPEN = 'lock-open';
    case SHIELD_CHECK = 'shield-check';
    case KEY = 'key';

    // Other
    case STAR = 'star';
    case SPARKLES = 'sparkles';
    case FIRE = 'fire';
    case MEGAPHONE = 'megaphone';
    case BELL = 'bell';
    case FLAG = 'flag';
    case TAG = 'tag';
    case TICKET = 'ticket';

    public function getLabel(): string
    {
        return match ($this) {
            // Communication & Social
            self::ENVELOPE => 'E-Mail (Umschlag)',
            self::PHONE => 'Telefon',
            self::CHAT_BUBBLE_LEFT_RIGHT => 'Chat',
            self::AT_SYMBOL => '@-Symbol',

            // Social Media & Web
            self::GLOBE_ALT => 'Weltkugel',
            self::LINK => 'Link',
            self::SHARE => 'Teilen',

            // Location & Navigation
            self::MAP_PIN => 'Standort-Pin',
            self::MAP => 'Karte',
            self::HOME => 'Haus',
            self::BUILDING_OFFICE => 'Gebäude',

            // Users & People
            self::USER => 'Benutzer',
            self::USER_GROUP => 'Benutzergruppe',
            self::USERS => 'Benutzer (Mehrere)',
            self::HEART => 'Herz',

            // Calendar & Time
            self::CALENDAR => 'Kalender',
            self::CALENDAR_DAYS => 'Kalender (Tage)',
            self::CLOCK => 'Uhr',

            // Information & Support
            self::INFORMATION_CIRCLE => 'Information',
            self::QUESTION_MARK_CIRCLE => 'Frage',
            self::EXCLAMATION_CIRCLE => 'Warnung',
            self::LIGHT_BULB => 'Glühbirne',

            // Actions
            self::ARROW_RIGHT => 'Pfeil Rechts',
            self::ARROW_LEFT => 'Pfeil Links',
            self::ARROW_UP => 'Pfeil Oben',
            self::ARROW_DOWN => 'Pfeil Unten',
            self::CHEVRON_RIGHT => 'Chevron Rechts',
            self::CHEVRON_LEFT => 'Chevron Links',
            self::CHEVRON_UP => 'Chevron Oben',
            self::CHEVRON_DOWN => 'Chevron Unten',
            self::PLUS => 'Plus',
            self::MINUS => 'Minus',
            self::X_MARK => 'X',
            self::CHECK => 'Häkchen',
            self::CHECK_CIRCLE => 'Häkchen (Kreis)',

            // Media & Content
            self::PHOTO => 'Foto',
            self::CAMERA => 'Kamera',
            self::VIDEO_CAMERA => 'Videokamera',
            self::DOCUMENT => 'Dokument',
            self::NEWSPAPER => 'Zeitung',
            self::BOOK_OPEN => 'Buch (Offen)',

            // Commerce & Business
            self::SHOPPING_BAG => 'Einkaufstasche',
            self::SHOPPING_CART => 'Einkaufswagen',
            self::CREDIT_CARD => 'Kreditkarte',
            self::CURRENCY_EURO => 'Euro',
            self::BANKNOTES => 'Geldscheine',

            // Settings & Tools
            self::COG_6_TOOTH => 'Zahnrad',
            self::WRENCH => 'Schraubenschlüssel',
            self::ADJUSTMENTS_HORIZONTAL => 'Einstellungen',

            // Security
            self::LOCK_CLOSED => 'Schloss (Geschlossen)',
            self::LOCK_OPEN => 'Schloss (Offen)',
            self::SHIELD_CHECK => 'Schild (Häkchen)',
            self::KEY => 'Schlüssel',

            // Other
            self::STAR => 'Stern',
            self::SPARKLES => 'Funken',
            self::FIRE => 'Feuer',
            self::MEGAPHONE => 'Megaphon',
            self::BELL => 'Glocke',
            self::FLAG => 'Flagge',
            self::TAG => 'Tag',
            self::TICKET => 'Ticket',
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

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $icon): array => [
$icon->value => $icon->getLabel()
])
            ->toArray();
    }
}
