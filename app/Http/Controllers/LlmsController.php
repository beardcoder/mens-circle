<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SocialLinkType;
use App\Models\Event;
use App\Models\NewsletterSubscription;
use App\Models\Page;
use App\Models\Testimonial;
use App\Services\LlmsContentFormatter;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

final class LlmsController
{
    public function __construct(
        private readonly GeneralSettings $settings,
        private readonly LlmsContentFormatter $formatter,
    ) {}

    public function show(): Response
    {
        return response($this->generateLlmsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    private function generateLlmsTxt(): string
    {
        $publishedPages = Page::query()->with('contentBlocks')->published()->orderBy('title')->get();

        $lines = [
            ...$this->generateHeader(),
            ...$this->generateAboutSection(),
            ...$this->generateStatistics(),
            ...$this->generateUpcomingEvents(),
            ...$this->generatePastEvents(),
            ...$this->generatePageContent($publishedPages),
            ...$this->generateFaqSection($publishedPages),
            ...$this->generateTestimonials(),
            ...$this->generateLegalSection(),
            ...$this->generateActionsSection(),
        ];

        return implode("\n", $lines);
    }

    /**
     * @return array<int, string>
     */
    private function generateHeader(): array
    {
        $siteName = $this->settings->site_name ?? 'Maennerkreis Niederbayern';
        $siteDescription =
            $this->settings->site_description
            ?? 'Authentischer Austausch, Gemeinschaft und persoenliches Wachstum fuer Maenner in Niederbayern.';

        $lines = ['# ' . $siteName, ''];

        $tagline = $this->settings->site_tagline ?? '';
        if ($tagline !== '' && $tagline !== '0') {
            $lines[] = '> ' . $tagline;
            $lines[] = '';
        }

        $lines[] = '**Beschreibung:** ' . $siteDescription;
        $lines[] = '';
        $lines[] = '**Letzte Aktualisierung:** ' . now()->format('d.m.Y H:i') . ' Uhr';
        $lines[] = '**Website:** ' . url('/');

        foreach ([
            'Kontakt E-Mail' => $this->settings->contact_email ?? null,
            'Telefon' => $this->settings->contact_phone ?? null,
            'Standort' => $this->settings->location ?? null,
            'WhatsApp Community' => $this->settings->whatsapp_community_link ?? null,
        ] as $label => $value) {
            if (!$value) {
                continue;
            }

            $lines[] = "**{$label}:** {$value}";
        }

        return [...$lines, '', '---', ''];
    }

    /**
     * @return array<int, string>
     */
    private function generateAboutSection(): array
    {
        $siteName = $this->settings->site_name ?? 'Maennerkreis Niederbayern';

        $lines = ['## Ueber ' . $siteName, ''];
        $lines[] = 'Der Maennerkreis Niederbayern ist eine Gemeinschaft fuer Maenner, die sich regelmaessig zu Veranstaltungen treffen. Die Website bietet Informationen zu kommenden Events, Anmeldung zu Veranstaltungen und einen Newsletter-Service.';
        $lines[] = '';

        if ($this->settings->footer_text ?? false) {
            $lines[] = strip_tags($this->settings->footer_text);
            $lines[] = '';
        }

        $socialLinks = $this->settings->social_links ?? [];
        if ($socialLinks !== []) {
            $lines[] = '### Social Media';
            $lines[] = '';
            foreach ($socialLinks as $data) {
                /** @var array<string, mixed> $data */
                $url = $data['value'] ?? null;
                $platformName = $data['label'] ?? SocialLinkType::tryFrom((string) ($data['type'] ?? ''))?->getLabel() ?? null;

                if ($url && \is_string($url) && \is_string($platformName)) {
                    $lines[] = "- **{$platformName}:** {$url}";
                }
            }

            $lines[] = '';
        }

        return [...$lines, '---', ''];
    }

    /**
     * @return array<int, string>
     */
    private function generateStatistics(): array
    {
        $totalTestimonials = Testimonial::published()->count();
        $totalSubscribers = NewsletterSubscription::activeCount();

        $eventStats = Event::query()
            ->published()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN event_date >= ? THEN 1 ELSE 0 END) as upcoming', [now()])
            ->selectRaw('SUM(CASE WHEN event_date < ? THEN 1 ELSE 0 END) as past', [now()])
            ->first();

        $lines = [
            '## Statistiken',
            '',
            '- **Gesamtanzahl Veranstaltungen:** ' . (int) ($eventStats->total ?? 0),
            '- **Kommende Veranstaltungen:** ' . (int) ($eventStats->upcoming ?? 0),
            '- **Vergangene Veranstaltungen:** ' . (int) ($eventStats->past ?? 0),
        ];

        if ($totalTestimonials > 0) {
            $lines[] = '- **Veroeffentlichte Erfahrungsberichte:** ' . $totalTestimonials;
        }

        if ($totalSubscribers > 0) {
            $lines[] = '- **Newsletter-Abonnenten:** ' . $totalSubscribers;
        }

        return [...$lines, '', '---', ''];
    }

    /**
     * @return array<int, string>
     */
    private function generateUpcomingEvents(): array
    {
        $events = Event::published()->upcoming()->orderBy('event_date')->get();

        $lines = ['## Kommende Veranstaltungen', ''];

        if ($events->isEmpty()) {
            return [...$lines, 'Aktuell sind keine kommenden Veranstaltungen geplant.', ''];
        }

        foreach ($events as $event) {
            $lines[] = '### ' . $event->title;
            $lines[] = '';
            $lines[] = '**Datum:** ' . $event->event_date->format('d.m.Y');
            $lines[] = '**Uhrzeit:** ' . $event->start_time->format('H:i') . ' - ' . $event->end_time->format('H:i') . ' Uhr';

            if ($event->location) {
                $lines[] = '**Ort:** ' . $event->location;
            }

            if ($event->fullAddress) {
                $lines[] = '**Adresse:** ' . $event->fullAddress;
            }

            $lines[] = "**Verfuegbare Plaetze:** {$event->availableSpots} von {$event->max_participants}";

            if ($event->cost_basis) {
                $lines[] = '**Kostenbeitrag:** ' . $event->cost_basis;
            }

            if ($event->description) {
                $lines[] = '';
                $lines[] = $this->formatter->convertHtmlToMarkdown($event->description);
            }

            $lines[] = '';
            $lines[] = '**Mehr Informationen und Anmeldung:** ' . route('event.show.slug', $event->slug);
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generatePastEvents(): array
    {
        $events = Event::published()->where('event_date', '<', now())->orderByDesc('event_date')->limit(5)->get();

        if ($events->isEmpty()) {
            return [];
        }

        $lines = ['## Vergangene Veranstaltungen (letzte 5)', ''];

        foreach ($events as $event) {
            $url = route('event.show.slug', $event->slug);
            $date = $event->event_date->format('d.m.Y');
            $lines[] = "- **[{$event->title}]({$url})** - {$date}";
        }

        return [...$lines, '', '---', ''];
    }

    /**
     * @param Collection<int, Page> $publishedPages
     *
     * @return array<int, string>
     */
    private function generatePageContent(Collection $publishedPages): array
    {
        $lines = [
            '## Seiteninhalte',
            '',
            '### Startseite',
            '',
            'Die Hauptseite mit einem Ueberblick ueber den Maennerkreis Niederbayern.',
            '',
            '**URL:** ' . route('home'),
            '',
        ];

        $pages = $publishedPages->whereNotIn('slug', ['home', 'impressum', 'datenschutz']);

        foreach ($pages as $page) {
            $lines[] = '### ' . $page->title;
            $lines[] = '';
            $lines[] = '**URL:** ' . route('page.show', $page->slug);
            $lines[] = '';

            foreach ($page->contentBlocks as $block) {
                $lines = [...$lines, ...$this->formatter->formatContentBlock($block)];
            }

            $lines[] = '---';
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param Collection<int, Page> $publishedPages
     *
     * @return array<int, string>
     */
    private function generateFaqSection(Collection $publishedPages): array
    {
        $faqBlocks = $this->extractFaqBlocks($publishedPages);

        if ($faqBlocks === []) {
            return [];
        }

        $lines = ['## Haeufig gestellte Fragen (FAQ)', ''];

        foreach ($faqBlocks as $faq) {
            $items = $faq['items'] ?? null;

            if (!\is_array($items) || $items === []) {
                continue;
            }

            $title = $this->formatter->stringField($faq, 'title');
            if ($title !== null) {
                $lines[] = '### ' . $this->formatter->convertHtmlToMarkdown($title);
                $lines[] = '';
            }

            $intro = $this->formatter->stringField($faq, 'intro');
            if ($intro !== null) {
                $lines[] = $intro;
                $lines[] = '';
            }

            foreach ($items as $item) {
                if (!\is_array($item)) {
                    continue;
                }

                $question = $this->formatter->stringField($item, 'question');
                $answer = $this->formatter->stringField($item, 'answer');

                if ($question === null || $answer === null) {
                    continue;
                }

                $lines[] = "**Q: {$question}**";
                $lines[] = '';
                $lines[] = '**A:** ' . $this->formatter->convertHtmlToMarkdown($answer);
                $lines[] = '';
            }
        }

        return [...$lines, '---', ''];
    }

    /**
     * @return array<int, string>
     */
    private function generateTestimonials(): array
    {
        $testimonials = Testimonial::published()->get();

        if ($testimonials->isEmpty()) {
            return [];
        }

        $lines = ['## Erfahrungsberichte', '', 'Was Teilnehmer ueber den Maennerkreis sagen:', ''];

        foreach ($testimonials as $testimonial) {
            $lines[] = '> ' . $testimonial->quote;
            $lines[] = '';

            $author = $testimonial->author_name;
            if ($testimonial->role) {
                $author .= ', ' . $testimonial->role;
            }

            $lines[] = "-- **{$author}**";
            $lines[] = '';
        }

        return [...$lines, '---', ''];
    }

    /**
     * @return array<int, string>
     */
    private function generateLegalSection(): array
    {
        return [
            '## Rechtliche Informationen',
            '',
            '- **[Impressum](' . route('page.show', 'impressum') . '):** Rechtliche Angaben und Anbieterkennzeichnung',
            '- **[Datenschutz](' . route('page.show', 'datenschutz') . '):** Datenschutzerklaerung gemaess DSGVO',
            '',
            '---',
            '',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function generateActionsSection(): array
    {
        $lines = [
            '## Verfuegbare Aktionen auf der Website',
            '',
            '1. **Newsletter-Anmeldung**',
            '   - Ueber das Formular auf der Startseite koennen sich Interessierte fuer den Newsletter anmelden',
            '   - Regelmaessige Updates zu neuen Veranstaltungen und Neuigkeiten',
            '',
            '2. **Veranstaltungsanmeldung**',
            '   - Ueber die jeweilige Veranstaltungsseite koennen sich Teilnehmer direkt registrieren',
            '   - Online-Formular mit persoenlichen Daten und optionalen Notizen',
            '',
            '3. **Erfahrungsbericht teilen**',
            '   - Teilnehmer koennen ihre Erfahrungen ueber ein Formular einreichen',
            '   - Nach Pruefung werden Erfahrungsberichte auf der Website veroeffentlicht',
            '',
        ];

        if ($this->settings->whatsapp_community_link ?? false) {
            $lines[] = '4. **WhatsApp Community beitreten**';
            $lines[] = '   - Direkter Austausch mit anderen Teilnehmern';
            $lines[] = '   - Link: ' . $this->settings->whatsapp_community_link;
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param Collection<int, Page> $publishedPages
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractFaqBlocks(Collection $publishedPages): array
    {
        $faqBlocks = [];

        foreach ($publishedPages as $page) {
            foreach ($page->contentBlocks as $block) {
                if ($block->type !== 'faq') {
                    continue;
                }

                $items = $block->data['items'] ?? null;
                if (!\is_array($items) || $items === []) {
                    continue;
                }

                $faqBlocks[] = $block->data;
            }
        }

        return $faqBlocks;
    }
}
