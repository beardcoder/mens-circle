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

    public function icon(): string
    {
        return match ($this) {
            self::Hero => '🎭',
            self::Intro => '👋',
            self::TextSection => '📝',
            self::ValueItems => '⭐',
            self::Moderator => '👤',
            self::JourneySteps => '🚀',
            self::Faq => '❓',
            self::Newsletter => '📧',
            self::Cta => '📣',
        };
    }

    public function labelWithIcon(): string
    {
        return $this->icon().' '.$this->label();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public function hasEyebrow(): bool
    {
        return in_array($this, [
            self::Intro,
            self::TextSection,
            self::ValueItems,
            self::Moderator,
            self::JourneySteps,
            self::Faq,
            self::Newsletter,
            self::Cta,
        ], true);
    }

    public function hasHtmlTitle(): bool
    {
        return in_array($this, [
            self::Hero,
            self::Intro,
            self::JourneySteps,
            self::Faq,
            self::Newsletter,
            self::Cta,
        ], true);
    }

    public function hasTitle(): bool
    {
        return in_array($this, [
            self::Hero,
            self::Intro,
            self::TextSection,
            self::ValueItems,
            self::JourneySteps,
            self::Faq,
            self::Newsletter,
            self::Cta,
        ], true);
    }

    public function hasSmallTitle(): bool
    {
        return in_array($this, [
            self::TextSection,
            self::ValueItems,
        ], true);
    }

    public function hasText(): bool
    {
        return in_array($this, [
            self::Intro,
            self::Newsletter,
            self::Cta,
        ], true);
    }

    public function hasQuote(): bool
    {
        return in_array($this, [
            self::Intro,
            self::Moderator,
        ], true);
    }

    public function hasButton(): bool
    {
        return in_array($this, [
            self::Hero,
            self::Cta,
        ], true);
    }

    public function hasImage(): bool
    {
        return in_array($this, [
            self::Hero,
            self::Moderator,
        ], true);
    }

    public function hasItemsRepeater(): bool
    {
        return in_array($this, [
            self::ValueItems,
            self::Faq,
        ], true);
    }
}
