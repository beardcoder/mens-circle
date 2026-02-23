<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumOptions;

enum EmailTemplate: string
{
    use HasEnumOptions;

    case NewsletterNewEvent = 'newsletter_new_event';
    case NewsletterEventReminder = 'newsletter_event_reminder';
    case ParticipantPreEvent = 'participant_pre_event';

    public function getLabel(): string
    {
        return match ($this) {
            self::NewsletterNewEvent => 'Neues Event ankündigen',
            self::NewsletterEventReminder => 'Event-Erinnerung (Plätze frei)',
            self::ParticipantPreEvent => 'Einstimmung vor dem Event',
        };
    }

    public function getCategory(): string
    {
        return match ($this) {
            self::NewsletterNewEvent, self::NewsletterEventReminder => 'newsletter',
            self::ParticipantPreEvent => 'participant',
        };
    }

    public function getSubject(): string
    {
        return match ($this) {
            self::NewsletterNewEvent => 'Neues Treffen: {event_title} am {event_date}',
            self::NewsletterEventReminder => 'Bald ist es soweit – {event_title} am {event_date}',
            self::ParticipantPreEvent => '{event_title} steht bevor – wir freuen uns auf dich',
        };
    }

    public function getContent(): string
    {
        return match ($this) {
            self::NewsletterNewEvent => $this->newsletterNewEventContent(),
            self::NewsletterEventReminder => $this->newsletterEventReminderContent(),
            self::ParticipantPreEvent => $this->participantPreEventContent(),
        };
    }

    /**
     * @return array<string>
     */
    public static function placeholders(): array
    {
        return [
            '{event_title}',
            '{event_date}',
            '{event_time}',
            '{event_location}',
            '{event_url}',
            '{available_spots}',
            '{cost_basis}',
            '{site_name}',
        ];
    }

    /**
     * @return array<string, EmailTemplate>
     */
    public static function newsletterTemplates(): array
    {
        return array_filter(
            array_combine(array_map(fn (self $case): string => $case->value, self::cases()), self::cases(),),
            fn (self $case): bool => $case->getCategory() === 'newsletter',
        );
    }

    /**
     * @return array<string, EmailTemplate>
     */
    public static function participantTemplates(): array
    {
        return array_filter(
            array_combine(array_map(fn (self $case): string => $case->value, self::cases()), self::cases(),),
            fn (self $case): bool => $case->getCategory() === 'participant',
        );
    }

    private function newsletterNewEventContent(): string
    {
        return <<<'HTML'
        <h2>Ein neues Treffen steht an!</h2>

        <p>Wir laden dich herzlich zu unserem nächsten Männerkreis ein.</p>

        <p><strong>{event_title}</strong><br>
        📅 {event_date} um {event_time} Uhr<br>
        📍 {event_location}</p>

        <p>Der Männerkreis ist ein geschützter Raum, in dem wir als Männer zusammenkommen – offen, ehrlich und ohne Masken. Egal ob du zum ersten Mal dabei bist oder schon länger Teil unserer Gemeinschaft: Du bist willkommen.</p>

        <p><strong>Teilnahme:</strong> {cost_basis}</p>

        <p>Es sind noch <strong>{available_spots} Plätze</strong> frei. Sichere dir jetzt deinen Platz:</p>

        <p>👉 <a href="{event_url}">Jetzt anmelden</a></p>

        <p>Wir freuen uns auf dich!</p>
        HTML;
    }

    private function newsletterEventReminderContent(): string
    {
        return <<<'HTML'
        <h2>Unser Treffen rückt näher!</h2>

        <p>Nur noch wenige Tage bis zu unserem nächsten Männerkreis – und es gibt noch freie Plätze.</p>

        <p><strong>{event_title}</strong><br>
        📅 {event_date} um {event_time} Uhr<br>
        📍 {event_location}</p>

        <p>Noch <strong>{available_spots} Plätze</strong> verfügbar.</p>

        <p>Vielleicht hast du schon länger überlegt, mal vorbeizukommen? Jetzt ist ein guter Zeitpunkt. Der Männerkreis lebt von den Männern, die sich trauen, aufzutauchen – so wie sie sind.</p>

        <p><strong>Teilnahme:</strong> {cost_basis}</p>

        <p>👉 <a href="{event_url}">Jetzt Platz sichern</a></p>

        <p>Wir freuen uns, wenn du dabei bist.</p>
        HTML;
    }

    private function participantPreEventContent(): string
    {
        return <<<'HTML'
        <h2>Es ist bald soweit!</h2>

        <p>In wenigen Tagen treffen wir uns wieder zum Männerkreis – und du bist dabei. Das freut uns sehr.</p>

        <p><strong>{event_title}</strong><br>
        📅 {event_date} um {event_time} Uhr<br>
        📍 {event_location}</p>

        <h3>Nimm dir einen Moment</h3>

        <p>Bevor wir uns treffen, möchten wir dich einladen, kurz innezuhalten:</p>

        <ul>
        <li>Wie geht es dir gerade – wirklich?</li>
        <li>Was beschäftigt dich in diesen Tagen?</li>
        <li>Was möchtest du loslassen, was möchtest du mitnehmen in den Kreis?</li>
        </ul>

        <p>Du musst nichts vorbereiten. Komm einfach so, wie du bist. Der Kreis hält dich.</p>

        <h3>Zur Erinnerung</h3>

        <ul>
        <li>Komm pünktlich – wir starten gemeinsam</li>
        <li>Bring eine offene Haltung mit</li>
        <li>Falls du doch nicht kannst, gib uns bitte kurz Bescheid</li>
        </ul>

        <p><strong>Teilnahme:</strong> {cost_basis}</p>

        <p>👉 <a href="{event_url}">Alle Details zum Treffen</a></p>

        <p>Bis bald – wir freuen uns auf dich.</p>
        HTML;
    }
}
