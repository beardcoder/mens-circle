<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmailTemplate;
use App\Models\Event;

class EmailTemplateService
{
    /**
     * Resolve all placeholders in a template for the given event.
     *
     * @return array{subject: string, content: string}
     */
    public function resolve(EmailTemplate $template, ?Event $event = null): array
    {
        $event ??= Event::nextEvent();

        $replacements = $this->buildReplacements($event);

        return [
            'subject' => str_replace(array_keys($replacements), array_values($replacements), $template->getSubject()),
            'content' => str_replace(array_keys($replacements), array_values($replacements), $template->getContent()),
        ];
    }

    /**
     * Replace placeholders in arbitrary subject and content strings.
     *
     * @return array{subject: string, content: string}
     */
    public function replacePlaceholders(string $subject, string $content, ?Event $event = null): array
    {
        $event ??= Event::nextEvent();

        $replacements = $this->buildReplacements($event);

        return [
            'subject' => str_replace(array_keys($replacements), array_values($replacements), $subject),
            'content' => str_replace(array_keys($replacements), array_values($replacements), $content),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacements(?Event $event): array
    {
        if (!$event) {
            $eventPlaceholders = array_values(array_filter(
                EmailTemplate::placeholders(),
                fn (string $p): bool => $p !== '{first_name}',
            ));

            return array_fill_keys($eventPlaceholders, '—');
        }

        $eventUrl = route('event.show.slug', [
'slug' => $event->slug
]);

        /** @var string $siteName */
        $siteName = config('app.name', '');

        return [
            '{event_title}' => $event->title,
            '{event_date}' => $event->event_date->translatedFormat('l, d. F Y'),
            '{event_time}' => $event->start_time->format('H:i'),
            '{event_location}' => $event->location ?? '—',
            '{event_url}' => $eventUrl,
            '{available_spots}' => (string) $event->availableSpots,
            '{cost_basis}' => $event->cost_basis ?? '—',
            '{site_name}' => $siteName,
        ];
    }
}
