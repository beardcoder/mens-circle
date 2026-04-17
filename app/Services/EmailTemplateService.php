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
        return $this->replacePlaceholders($template->getSubject(), $template->getContent(), $event);
    }

    /**
     * Replace placeholders in arbitrary subject and content strings.
     *
     * @return array{subject: string, content: string}
     */
    public function replacePlaceholders(string $subject, string $content, ?Event $event = null): array
    {
        $replacements = $this->buildReplacements($event ?? Event::nextEvent());

        return [
            'subject' => strtr($subject, $replacements),
            'content' => strtr($content, $replacements),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacements(?Event $event): array
    {
        if (!$event instanceof Event) {
            $eventPlaceholders = array_values(array_filter(
                EmailTemplate::placeholders(),
                static fn(string $p): bool => $p !== '{first_name}',
            ));

            return array_fill_keys($eventPlaceholders, '—');
        }

        /** @var string $siteName */
        $siteName = config('app.name', '');

        return [
            '{event_title}' => $event->title,
            '{event_date}' => $event->event_date->translatedFormat('l, d. F Y'),
            '{event_time}' => $event->start_time->format('H:i'),
            '{event_location}' => $event->location ?? '—',
            '{event_url}' => route('event.show.slug', ['slug' => $event->slug]),
            '{available_spots}' => (string) $event->availableSpots,
            '{cost_basis}' => $event->cost_basis ?? '—',
            '{site_name}' => $siteName,
        ];
    }
}
