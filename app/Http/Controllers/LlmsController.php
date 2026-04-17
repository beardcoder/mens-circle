<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\NewsletterSubscription;
use App\Models\Page;
use App\Models\Testimonial;
use App\Settings\GeneralSettings;
use Illuminate\Http\Response;

final class LlmsController
{
    public function __construct(
        private readonly GeneralSettings $settings,
    ) {}

    public function show(): Response
    {
        return response($this->generateLlmsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    private function generateLlmsTxt(): string
    {
        $lines = [
            ...$this->generateHeader(),
            ...$this->generateAboutSection(),
            ...$this->generateStatistics(),
            ...$this->generateUpcomingEvents(),
            ...$this->generatePastEvents(),
            ...$this->generatePageContent(),
            ...$this->generateFaqSection(),
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
        $siteDescription = $this->settings->site_description
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
            if ($value) {
                $lines[] = "**{$label}:** {$value}";
            }
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

        if (isset($this->settings->social_links) && $this->settings->social_links !== []) {
            $lines[] = '### Social Media';
            $lines[] = '';
            foreach ($this->settings->social_links as $platform => $data) {
                /** @var array<string, mixed> $data */
                $url = $data['url'] ?? $data['link'] ?? null;
                $platformName = $data['platform'] ?? $data['name'] ?? ucfirst((string) $platform);

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

        $lines = [
            '## Statistiken',
            '',
            '- **Gesamtanzahl Veranstaltungen:** ' . Event::published()->count(),
            '- **Kommende Veranstaltungen:** ' . Event::published()->upcoming()->count(),
            '- **Vergangene Veranstaltungen:** ' . Event::published()->where('event_date', '<', now())->count(),
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
                $lines[] = $this->convertHtmlToMarkdown($event->description);
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
        $events = Event::published()
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

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
     * @return array<int, string>
     */
    private function generatePageContent(): array
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

        $pages = Page::with('contentBlocks')
            ->published()
            ->whereNotIn('slug', ['home', 'impressum', 'datenschutz'])
            ->orderBy('title')
            ->get();

        foreach ($pages as $page) {
            $lines[] = '### ' . $page->title;
            $lines[] = '';
            $lines[] = '**URL:** ' . route('page.show', $page->slug);
            $lines[] = '';

            foreach ($page->contentBlocks as $block) {
                $lines = [...$lines, ...$this->formatContentBlock($block)];
            }

            $lines[] = '---';
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generateFaqSection(): array
    {
        $faqBlocks = $this->extractFaqBlocks();

        if ($faqBlocks === []) {
            return [];
        }

        $lines = ['## Haeufig gestellte Fragen (FAQ)', ''];

        foreach ($faqBlocks as $faq) {
            if (!isset($faq['items'])) {
                continue;
            }

            if (!\is_array($faq['items'])) {
                continue;
            }

            if ($faq['items'] === []) {
                continue;
            }

            if ($title = $this->stringField($faq, 'title')) {
                $lines[] = '### ' . $this->convertHtmlToMarkdown($title);
                $lines[] = '';
            }

            if ($intro = $this->stringField($faq, 'intro')) {
                $lines[] = $intro;
                $lines[] = '';
            }

            foreach ($faq['items'] as $item) {
                if (!\is_array($item)) {
                    continue;
                }

                $question = $this->stringField($item, 'question');
                $answer = $this->stringField($item, 'answer');
                if (!$question) {
                    continue;
                }

                if (!$answer) {
                    continue;
                }

                $lines[] = "**Q: {$question}**";
                $lines[] = '';
                $lines[] = '**A:** ' . $this->convertHtmlToMarkdown($answer);
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
     * @return array<int, string>
     */
    private function formatContentBlock(ContentBlock $block): array
    {
        /** @var array<string, mixed> $data */
        $data = $block->data;

        return match ($block->type) {
            'hero' => $this->formatHeroBlock($data),
            'text_section' => $this->formatTextSectionBlock($data),
            'intro' => $this->formatIntroBlock($data),
            'value_items', 'archetypes', 'journey_steps' => $this->formatItemsBlock($data),
            'moderator' => $this->formatModeratorBlock($data),
            'cta' => $this->formatCtaBlock($data),
            'newsletter' => $this->formatNewsletterBlock($data),
            'whatsapp_community' => $this->formatWhatsappBlock(),
            'testimonials' => [
                '#### Erfahrungsberichte', '',
                '*Dieser Bereich zeigt automatisch veroeffentlichte Erfahrungsberichte von Teilnehmern.*', '',
            ],
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatHeroBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'label');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'description');

        return $this->appendLinkAction($lines, $data, 'button_text', 'button_link', 'Call-to-Action');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatTextSectionBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');

        if ($title = $this->stringField($data, 'title')) {
            $lines[] = '#### ' . $title;
            $lines[] = '';
        }

        if ($content = $this->stringField($data, 'content')) {
            $lines[] = $this->convertHtmlToMarkdown($content);
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatIntroBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'text');

        if ($quote = $this->stringField($data, 'quote')) {
            $lines[] = '> ' . $this->convertHtmlToMarkdown($quote);
            $lines[] = '';
        }

        if (isset($data['values']) && \is_array($data['values'])) {
            return $this->appendItemList($lines, $data['values']);
        }

        return $lines;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatItemsBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'subtitle');
        $lines = $this->appendParagraph($lines, $data, 'intro');

        $items = $data['items'] ?? $data['steps'] ?? [];
        if (\is_array($items)) {
            return $this->appendItemList($lines, $items);
        }

        return $lines;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatModeratorBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'name');

        if ($bio = $this->stringField($data, 'bio')) {
            $lines[] = $this->convertHtmlToMarkdown($bio);
            $lines[] = '';
        }

        if ($quote = $this->stringField($data, 'quote')) {
            $lines[] = '> ' . $quote;
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatCtaBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'text');

        return $this->appendLinkAction($lines, $data, 'button_text', 'button_link', 'Aktion');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    private function formatNewsletterBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'text');

        return [...$lines, '*Besucher koennen sich hier fuer den Newsletter anmelden.*', ''];
    }

    /**
     * @return array<int, string>
     */
    private function formatWhatsappBlock(): array
    {
        $lines = [
            '#### WhatsApp Community',
            '',
            'Tritt unserer WhatsApp Community bei und vernetze dich mit anderen Maennern aus der Region.',
            '',
        ];

        if ($this->settings->whatsapp_community_link ?? false) {
            $lines[] = '**Link zur Community:** ' . $this->settings->whatsapp_community_link;
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractFaqBlocks(): array
    {
        $faqBlocks = [];

        foreach (Page::with('contentBlocks')->published()->get() as $page) {
            foreach ($page->contentBlocks as $block) {
                if ($block->type === 'faq' && !empty($block->data['items'] ?? null)) {
                    $faqBlocks[] = $block->data;
                }
            }
        }

        return $faqBlocks;
    }

    private function convertHtmlToMarkdown(string $html): string
    {
        if ($html === '' || $html === '0') {
            return '';
        }

        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        $patterns = [
            '/<h1[^>]*>(.*?)<\/h1>/is' => '# $1',
            '/<h2[^>]*>(.*?)<\/h2>/is' => '## $1',
            '/<h3[^>]*>(.*?)<\/h3>/is' => '### $1',
            '/<h4[^>]*>(.*?)<\/h4>/is' => '#### $1',
            '/<h5[^>]*>(.*?)<\/h5>/is' => '##### $1',
            '/<h6[^>]*>(.*?)<\/h6>/is' => '###### $1',
            '/<strong[^>]*>(.*?)<\/strong>/is' => '**$1**',
            '/<b[^>]*>(.*?)<\/b>/is' => '**$1**',
            '/<em[^>]*>(.*?)<\/em>/is' => '*$1*',
            '/<i[^>]*>(.*?)<\/i>/is' => '*$1*',
            '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is' => '[$2]($1)',
            '/<br[^>]*>/i' => "\n",
            '/<br\s*\/?>/i' => "\n",
            '/<p[^>]*>(.*?)<\/p>/is' => '$1' . "\n\n",
            '/<ul[^>]*>(.*?)<\/ul>/is' => '$1',
            '/<ol[^>]*>(.*?)<\/ol>/is' => '$1',
            '/<li[^>]*>(.*?)<\/li>/is' => '- $1' . "\n",
            '/<blockquote[^>]*>(.*?)<\/blockquote>/is' => '> $1',
            '/<code[^>]*>(.*?)<\/code>/is' => '`$1`',
            '/<pre[^>]*>(.*?)<\/pre>/is' => '```' . "\n" . '$1' . "\n" . '```',
            '/<hr[^>]*>/i' => '---',
            '/<span[^>]*class=["\']light["\'][^>]*>(.*?)<\/span>/is' => '$1',
        ];

        $markdown = preg_replace(array_keys($patterns), array_values($patterns), $html) ?? $html;
        $markdown = strip_tags($markdown);
        $markdown = preg_replace("/\n{3,}/", "\n\n", $markdown) ?? $markdown;
        $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($markdown);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function stringField(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $data
     *
     * @return array<int, string>
     */
    private function appendEyebrow(array $lines, array $data, string $key): array
    {
        if ($value = $this->stringField($data, $key)) {
            $lines[] = "*{$value}*";
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $data
     *
     * @return array<int, string>
     */
    private function appendHeading(array $lines, array $data, string $key): array
    {
        if ($value = $this->stringField($data, $key)) {
            $lines[] = '#### ' . $this->convertHtmlToMarkdown($value);
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $data
     *
     * @return array<int, string>
     */
    private function appendParagraph(array $lines, array $data, string $key): array
    {
        if ($value = $this->stringField($data, $key)) {
            $lines[] = $value;
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $data
     *
     * @return array<int, string>
     */
    private function appendLinkAction(array $lines, array $data, string $textKey, string $linkKey, string $label): array
    {
        $text = $this->stringField($data, $textKey);
        $link = $this->stringField($data, $linkKey);

        if ($text && $link) {
            $lines[] = \sprintf('**%s:** [%s](%s)', $label, $text, $link);
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $items
     *
     * @return array<int, string>
     */
    private function appendItemList(array $lines, array $items): array
    {
        foreach ($items as $item) {
            if (!\is_array($item)) {
                continue;
            }

            $title = $this->stringField($item, 'title');
            if (!$title) {
                continue;
            }

            $number = $item['number'] ?? null;
            $prefix = \is_scalar($number) && !\in_array($number, [null, '', 0, false], true) ? "{$number}. " : '- ';

            $lines[] = "{$prefix}**{$title}**";

            if ($description = $this->stringField($item, 'description')) {
                $lines[] = '  ' . $description;
            }

            $lines[] = '';
        }

        return $lines;
    }
}
