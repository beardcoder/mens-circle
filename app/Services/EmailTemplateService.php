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
        // Keep {first_name} literal — it is substituted per recipient downstream.
        $replacements = $this->buildReplacements($event ?? Event::nextEvent(), missing: '—');
        unset($replacements['{first_name}']);

        return [
            'subject' => strtr($subject, $replacements),
            'content' => strtr($content, $replacements),
        ];
    }

    /**
     * Render plain text for messengers (WhatsApp, Signal, Telegram).
     * Uses empty strings for missing values and removes lines whose only meaningful
     * content was a placeholder that resolved to empty, so the text never shows
     * raw placeholders or "Label: —" artefacts.
     */
    public function renderForMessenger(string $content, ?Event $event = null): string
    {
        $replacements = $this->buildReplacements($event ?? Event::nextEvent(), missing: '');
        $replacements['{first_name}'] = '';

        $stripped = $this->stripEmptyPlaceholderLines($content, $replacements);

        return $this->collapseBlankLines(strtr($stripped, $replacements));
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacements(?Event $event, string $missing): array
    {
        if (!$event instanceof Event) {
            return array_fill_keys(EmailTemplate::placeholders(), $missing);
        }

        /** @var string $siteName */
        $siteName = config('app.name', '');

        return [
            '{event_title}' => $event->title,
            '{event_date}' => $event->event_date->translatedFormat('l, d. F Y'),
            '{event_time}' => $event->start_time->format('H:i'),
            '{event_location}' => $event->location ?? $missing,
            '{event_url}' => route('event.show.slug', ['slug' => $event->slug]),
            '{available_spots}' => (string) $event->availableSpots,
            '{cost_basis}' => $event->cost_basis ?? $missing,
            '{site_name}' => $siteName,
        ];
    }

    /**
     * Drop template lines whose only meaningful content is a placeholder that
     * resolves to an empty string. Examples that get dropped when the value
     * is empty:
     *   "Teilnahme: {cost_basis}"
     *   "{event_location}"
     *
     * Lines like "Anmeldung:" (without an inline placeholder) are kept,
     * because they are intentional labels for content on the next line.
     *
     * @param array<string, string> $replacements
     */
    private function stripEmptyPlaceholderLines(string $template, array $replacements): string
    {
        $emptyPlaceholders = array_keys(array_filter($replacements, static fn(string $value): bool => trim($value) === ''));

        if ($emptyPlaceholders === []) {
            return $template;
        }

        $lines = preg_split('/\R/u', $template);

        if ($lines === false) {
            return $template;
        }

        $kept = [];

        foreach ($lines as $line) {
            $trimmed = rtrim($line);

            $isEmpty = false;

            // "Label: {placeholder}"
            if (preg_match('/^\s*\S[^:{]*:\s*(\{[a-z_]+\})\s*$/u', $trimmed, $matches)) {
                $isEmpty = in_array($matches[1], $emptyPlaceholders, true);
            }

            // "{placeholder}" alone on a line
            if (!$isEmpty && preg_match('/^\s*(\{[a-z_]+\})\s*$/u', $trimmed, $matches)) {
                $isEmpty = in_array($matches[1], $emptyPlaceholders, true);
            }

            if ($isEmpty) {
                continue;
            }

            $kept[] = $trimmed;
        }

        return implode("\n", $kept);
    }

    private function collapseBlankLines(string $text): string
    {
        return (string) preg_replace("/\n{3,}/", "\n\n", trim($text));
    }
}
