<?php

namespace App\Enums;

enum ContentBlockType: string
{
    case Hero = 'hero';
    case Intro = 'intro';
    case TextSection = 'text_section';
    case ValueItems = 'value_items';
    case Moderator = 'moderator';
    case JourneySteps = 'journey_steps';
    case Faq = 'faq';
    case Newsletter = 'newsletter';
    case Cta = 'cta';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero Bereich',
            self::Intro => 'Intro Bereich',
            self::TextSection => 'Text Bereich',
            self::ValueItems => 'Werte Liste',
            self::Moderator => 'Moderator Bereich',
            self::JourneySteps => 'Ablauf Schritte',
            self::Faq => 'FAQ Bereich',
            self::Newsletter => 'Newsletter Bereich',
            self::Cta => 'Call-to-Action',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
