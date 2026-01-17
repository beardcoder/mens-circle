<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use App\Models\Testimonial;
use App\Settings\GeneralSettings;
use Illuminate\Http\Response;

class LlmsController extends Controller
{
    public function show(): Response
    {
        return response($this->generateLlmsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    private function generateLlmsTxt(): string
    {
        $settings = app(GeneralSettings::class);
        $siteName = $settings->site_name ?? 'Männerkreis Niederbayern';
        $siteDescription = $settings->site_description ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.';

        $lines = [];

        // Header
        $lines[] = '# ' . $siteName;
        $lines[] = '';
        $lines[] = '> ' . $siteDescription;
        $lines[] = '';
        $lines[] = 'Der Männerkreis Niederbayern ist eine Gemeinschaft für Männer, die sich regelmäßig zu Veranstaltungen treffen. Die Website bietet Informationen zu kommenden Events, Anmeldung zu Veranstaltungen und einen Newsletter-Service.';
        $lines[] = '';

        // Upcoming Events Section
        $lines[] = '## Kommende Veranstaltungen';
        $lines[] = '';
        $upcomingEvents = Event::published()
            ->upcoming()
            ->orderBy('event_date')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $lines[] = 'Aktuell sind keine kommenden Veranstaltungen geplant.';
            $lines[] = '';
        } else {
            foreach ($upcomingEvents as $event) {
                $lines[] = '### ' . $event->title;
                $lines[] = '';
                $lines[] = '**Datum:** ' . $event->event_date->format('d.m.Y');

                if ($event->start_time && $event->end_time) {
                    $lines[] = '**Uhrzeit:** ' . $event->start_time->format('H:i') . ' - ' . $event->end_time->format('H:i') . ' Uhr';
                }

                if ($event->location) {
                    $lines[] = '**Ort:** ' . $event->location;
                }

                if ($event->fullAddress) {
                    $lines[] = '**Adresse:** ' . $event->fullAddress;
                }

                $lines[] = '**Verfügbare Plätze:** ' . $event->availableSpots . ' von ' . $event->max_participants;

                if ($event->description) {
                    $lines[] = '';
                    $lines[] = strip_tags($event->description);
                }

                $url = route('event.show.slug', $event->slug);
                $lines[] = '';
                $lines[] = '**Mehr Informationen und Anmeldung:** ' . $url;
                $lines[] = '';
                $lines[] = '---';
                $lines[] = '';
            }
        }

        // Past Events Section
        $pastEvents = Event::published()
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

        if ($pastEvents->isNotEmpty()) {
            $lines[] = '## Vergangene Veranstaltungen';
            $lines[] = '';
            foreach ($pastEvents as $event) {
                $date = $event->event_date->format('d.m.Y');
                $url = route('event.show.slug', $event->slug);
                $lines[] = sprintf('- **[%s](%s)** - %s', $event->title, $url, $date);
            }
            $lines[] = '';
        }

        // Pages with Content Section
        $lines[] = '## Seiteninhalte';
        $lines[] = '';
        $lines[] = '### Startseite';
        $lines[] = '';
        $lines[] = 'Die Hauptseite mit einem Überblick über den Männerkreis Niederbayern.';
        $lines[] = '';
        $lines[] = '**URL:** ' . route('home');
        $lines[] = '';

        $pages = Page::with('contentBlocks')
            ->published()
            ->whereNotIn('slug', ['home', 'impressum', 'datenschutz'])
            ->orderBy('title')
            ->get();

        foreach ($pages as $page) {
            $lines[] = '### ' . $page->title;
            $lines[] = '';
            $url = route('page.show', $page->slug);
            $lines[] = '**URL:** ' . $url;
            $lines[] = '';

            // Include content blocks information
            foreach ($page->contentBlocks as $block) {
                $lines = array_merge($lines, $this->formatContentBlock($block));
            }

            $lines[] = '---';
            $lines[] = '';
        }

        // FAQ Section
        $faqBlocks = $this->extractFaqBlocks();
        if (!empty($faqBlocks)) {
            $lines[] = '## Häufig gestellte Fragen (FAQ)';
            $lines[] = '';

            foreach ($faqBlocks as $faq) {
                if (!empty($faq['items'])) {
                    if (!empty($faq['title'])) {
                        $lines[] = '### ' . strip_tags($faq['title']);
                        $lines[] = '';
                    }

                    if (!empty($faq['intro'])) {
                        $lines[] = $faq['intro'];
                        $lines[] = '';
                    }

                    foreach ($faq['items'] as $item) {
                        if (!empty($item['question']) && !empty($item['answer'])) {
                            $lines[] = '**Q: ' . $item['question'] . '**';
                            $lines[] = '';
                            $lines[] = strip_tags($item['answer']);
                            $lines[] = '';
                        }
                    }
                }
            }

            $lines[] = '---';
            $lines[] = '';
        }

        // Testimonials Section
        $testimonials = Testimonial::published()->get();
        if ($testimonials->isNotEmpty()) {
            $lines[] = '## Erfahrungsberichte';
            $lines[] = '';
            $lines[] = 'Was Teilnehmer über den Männerkreis sagen:';
            $lines[] = '';

            foreach ($testimonials as $testimonial) {
                $lines[] = '> ' . $testimonial->quote;
                $lines[] = '';

                $author = $testimonial->author_name;
                if ($testimonial->role) {
                    $author .= ', ' . $testimonial->role;
                }

                $lines[] = '— **' . $author . '**';
                $lines[] = '';
            }

            $lines[] = '---';
            $lines[] = '';
        }

        // Legal Pages Section
        $lines[] = '## Rechtliche Informationen';
        $lines[] = '';
        $lines[] = '- **[Impressum](' . route('page.show', 'impressum') . '):** Rechtliche Angaben und Anbieterkennzeichnung';
        $lines[] = '- **[Datenschutz](' . route('page.show', 'datenschutz') . '):** Datenschutzerklärung gemäß DSGVO';
        $lines[] = '';

        // Actions Section
        $lines[] = '## Verfügbare Aktionen';
        $lines[] = '';
        $lines[] = '- **Newsletter-Anmeldung:** Über das Formular auf der Startseite können sich Interessierte für den Newsletter anmelden';
        $lines[] = '- **Veranstaltungsanmeldung:** Über die jeweilige Veranstaltungsseite können sich Teilnehmer registrieren';
        $lines[] = '- **Erfahrungsbericht teilen:** Teilnehmer können ihre Erfahrungen über ein Formular einreichen';

        return implode("\n", $lines);
    }

    /**
     * Format a content block for the llms.txt output
     *
     * @param \App\Models\ContentBlock $block
     * @return array
     */
    private function formatContentBlock($block): array
    {
        $lines = [];
        $data = $block->data;

        switch ($block->type) {
            case 'text_section':
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . strip_tags($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['content'])) {
                    $lines[] = strip_tags($data['content']);
                    $lines[] = '';
                }
                break;

            case 'intro':
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . strip_tags($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }
                if (!empty($data['quote'])) {
                    $lines[] = '> ' . strip_tags($data['quote']);
                    $lines[] = '';
                }
                break;

            case 'value_items':
            case 'journey_steps':
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . strip_tags($data['title']);
                    $lines[] = '';
                }

                $items = $data['items'] ?? $data['steps'] ?? [];
                if (!empty($items)) {
                    foreach ($items as $item) {
                        if (!empty($item['title'])) {
                            $prefix = !empty($item['number']) ? $item['number'] . '. ' : '- ';
                            $lines[] = $prefix . '**' . $item['title'] . '**';

                            if (!empty($item['description'])) {
                                $lines[] = '  ' . $item['description'];
                            }
                            $lines[] = '';
                        }
                    }
                }
                break;

            case 'moderator':
                if (!empty($data['name'])) {
                    $lines[] = '#### ' . strip_tags($data['name']);
                    $lines[] = '';
                }
                if (!empty($data['bio'])) {
                    $lines[] = strip_tags($data['bio']);
                    $lines[] = '';
                }
                if (!empty($data['quote'])) {
                    $lines[] = '> ' . $data['quote'];
                    $lines[] = '';
                }
                break;
        }

        return $lines;
    }

    /**
     * Extract FAQ blocks from all published pages
     *
     * @return array
     */
    private function extractFaqBlocks(): array
    {
        $faqBlocks = [];

        $pages = Page::with('contentBlocks')
            ->published()
            ->get();

        foreach ($pages as $page) {
            foreach ($page->contentBlocks as $block) {
                if ($block->type === 'faq' && !empty($block->data['items'])) {
                    $faqBlocks[] = $block->data;
                }
            }
        }

        return $faqBlocks;
    }
}
