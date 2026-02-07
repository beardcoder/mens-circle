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

class LlmsController extends Controller
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
        $siteDescription = $this->settings->site_description ?? 'Authentischer Austausch, Gemeinschaft und persoenliches Wachstum fuer Maenner in Niederbayern.';
        $siteTagline = $this->settings->site_tagline ?? '';

        $lines = [];
        $lines[] = '# ' . $siteName;
        $lines[] = '';

        if ($siteTagline !== '' && $siteTagline !== '0') {
            $lines[] = '> ' . $siteTagline;
            $lines[] = '';
        }

        $lines[] = '**Beschreibung:** ' . $siteDescription;
        $lines[] = '';
        $lines[] = '**Letzte Aktualisierung:** ' . now()->format('d.m.Y H:i') . ' Uhr';
        $lines[] = '**Website:** ' . url('/');

        if ($this->settings->contact_email ?? false) {
            $lines[] = '**Kontakt E-Mail:** ' . $this->settings->contact_email;
        }

        if ($this->settings->contact_phone ?? false) {
            $lines[] = '**Telefon:** ' . $this->settings->contact_phone;
        }

        if ($this->settings->location ?? false) {
            $lines[] = '**Standort:** ' . $this->settings->location;
        }

        if ($this->settings->whatsapp_community_link ?? false) {
            $lines[] = '**WhatsApp Community:** ' . $this->settings->whatsapp_community_link;
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generateAboutSection(): array
    {
        $siteName = $this->settings->site_name ?? 'Maennerkreis Niederbayern';

        $lines = [];
        $lines[] = '## Ueber ' . $siteName;
        $lines[] = '';
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

        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generateStatistics(): array
    {
        $totalEvents = Event::published()->count();
        $upcomingEventsCount = Event::published()->upcoming()->count();
        $pastEventsCount = Event::published()->where('event_date', '<', now())->count();
        $totalTestimonials = Testimonial::published()->count();
        $totalNewsletterSubscribers = NewsletterSubscription::whereNull('unsubscribed_at')->count();

        $lines = [];
        $lines[] = '## Statistiken';
        $lines[] = '';
        $lines[] = '- **Gesamtanzahl Veranstaltungen:** ' . $totalEvents;
        $lines[] = '- **Kommende Veranstaltungen:** ' . $upcomingEventsCount;
        $lines[] = '- **Vergangene Veranstaltungen:** ' . $pastEventsCount;

        if ($totalTestimonials > 0) {
            $lines[] = '- **Veroeffentlichte Erfahrungsberichte:** ' . $totalTestimonials;
        }

        if ($totalNewsletterSubscribers > 0) {
            $lines[] = '- **Newsletter-Abonnenten:** ' . $totalNewsletterSubscribers;
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generateUpcomingEvents(): array
    {
        $lines = [];
        $lines[] = '## Kommende Veranstaltungen';
        $lines[] = '';

        $upcomingEvents = Event::published()
            ->upcoming()
            ->orderBy('event_date')
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $lines[] = 'Aktuell sind keine kommenden Veranstaltungen geplant.';
            $lines[] = '';

            return $lines;
        }

        foreach ($upcomingEvents as $event) {
            $lines[] = '### ' . $event->title;
            $lines[] = '';
            $lines[] = '**Datum:** ' . $event->event_date->format('d.m.Y');
            $startTime = $event->start_time->format('H:i');
            $endTime = $event->end_time->format('H:i');
            $lines[] = "**Uhrzeit:** {$startTime} - {$endTime} Uhr";

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

            $url = url()->route('event.show.slug', $event->slug);
            $lines[] = '';
            $lines[] = '**Mehr Informationen und Anmeldung:** ' . $url;
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
        $pastEvents = Event::published()
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

        if ($pastEvents->isEmpty()) {
            return [];
        }

        $lines = [];
        $lines[] = '## Vergangene Veranstaltungen (letzte 5)';
        $lines[] = '';

        foreach ($pastEvents as $event) {
            $date = $event->event_date->format('d.m.Y');
            $url = url()->route('event.show.slug', $event->slug);
            $lines[] = "- **[{$event->title}]({$url})** - {$date}";
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generatePageContent(): array
    {
        $lines = [];
        $lines[] = '## Seiteninhalte';
        $lines[] = '';
        $lines[] = '### Startseite';
        $lines[] = '';
        $lines[] = 'Die Hauptseite mit einem Ueberblick ueber den Maennerkreis Niederbayern.';
        $lines[] = '';
        $lines[] = '**URL:** ' . url()->route('home');
        $lines[] = '';

        $pages = Page::with('contentBlocks')
            ->published()
            ->whereNotIn('slug', ['home', 'impressum', 'datenschutz'])
            ->orderBy('title')
            ->get();

        foreach ($pages as $page) {
            $lines[] = '### ' . $page->title;
            $lines[] = '';
            $url = url()->route('page.show', $page->slug);
            $lines[] = '**URL:** ' . $url;
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

        $lines = [];
        $lines[] = '## Haeufig gestellte Fragen (FAQ)';
        $lines[] = '';

        foreach ($faqBlocks as $faq) {
            if (empty($faq['items'])) {
                continue;
            }

            if (! \is_array($faq['items'])) {
                continue;
            }

            if (! empty($faq['title']) && \is_string($faq['title'])) {
                $lines[] = '### ' . $this->convertHtmlToMarkdown($faq['title']);
                $lines[] = '';
            }

            if (! empty($faq['intro']) && \is_string($faq['intro'])) {
                $lines[] = $faq['intro'];
                $lines[] = '';
            }

            foreach ($faq['items'] as $item) {
                if (! \is_array($item)) {
                    continue;
                }

                if (empty($item['question'])) {
                    continue;
                }

                if (! \is_string($item['question'])) {
                    continue;
                }

                if (empty($item['answer'])) {
                    continue;
                }

                if (! \is_string($item['answer'])) {
                    continue;
                }

                $lines[] = "**Q: {$item['question']}**";
                $lines[] = '';
                $lines[] = '**A:** ' . $this->convertHtmlToMarkdown($item['answer']);
                $lines[] = '';
            }
        }

        $lines[] = '---';
        $lines[] = '';

        return $lines;
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

        $lines = [];
        $lines[] = '## Erfahrungsberichte';
        $lines[] = '';
        $lines[] = 'Was Teilnehmer ueber den Maennerkreis sagen:';
        $lines[] = '';

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

        $lines[] = '---';
        $lines[] = '';

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function generateLegalSection(): array
    {
        return [
            '## Rechtliche Informationen',
            '',
            '- **[Impressum](' . url()->route(
                'page.show',
                'impressum',
            ) . '):** Rechtliche Angaben und Anbieterkennzeichnung',
            '- **[Datenschutz](' . url()->route(
                'page.show',
                'datenschutz',
            ) . '):** Datenschutzerklaerung gemaess DSGVO',
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
        $lines = [];
        $lines[] = '## Verfuegbare Aktionen auf der Website';
        $lines[] = '';
        $lines[] = '1. **Newsletter-Anmeldung**';
        $lines[] = '   - Ueber das Formular auf der Startseite koennen sich Interessierte fuer den Newsletter anmelden';
        $lines[] = '   - Regelmaessige Updates zu neuen Veranstaltungen und Neuigkeiten';
        $lines[] = '';
        $lines[] = '2. **Veranstaltungsanmeldung**';
        $lines[] = '   - Ueber die jeweilige Veranstaltungsseite koennen sich Teilnehmer direkt registrieren';
        $lines[] = '   - Online-Formular mit persoenlichen Daten und optionalen Notizen';
        $lines[] = '';
        $lines[] = '3. **Erfahrungsbericht teilen**';
        $lines[] = '   - Teilnehmer koennen ihre Erfahrungen ueber ein Formular einreichen';
        $lines[] = '   - Nach Pruefung werden Erfahrungsberichte auf der Website veroeffentlicht';
        $lines[] = '';

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
        $lines = [];
        /** @var array<string, mixed> $data */
        $data = $block->data;

        switch ($block->type) {
            case 'hero':
                if (! empty($data['label']) && \is_string($data['label'])) {
                    $lines[] = "*{$data['label']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }

                if (! empty($data['description']) && \is_string($data['description'])) {
                    $lines[] = $data['description'];
                    $lines[] = '';
                }

                $hasButtonText = ! empty($data['button_text']) && \is_string($data['button_text']);
                $hasButtonLink = ! empty($data['button_link']) && \is_string($data['button_link']);

                if ($hasButtonText && $hasButtonLink) {
                    \assert(\is_string($data['button_text']));
                    \assert(\is_string($data['button_link']));
                    $lines[] = \sprintf('**Call-to-Action:** [%s](%s)', $data['button_text'], $data['button_link']);
                    $lines[] = '';
                }

                break;

            case 'text_section':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $data['title'];
                    $lines[] = '';
                }

                if (! empty($data['content']) && \is_string($data['content'])) {
                    $lines[] = $this->convertHtmlToMarkdown($data['content']);
                    $lines[] = '';
                }

                break;

            case 'intro':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }

                if (! empty($data['text']) && \is_string($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }

                if (! empty($data['quote']) && \is_string($data['quote'])) {
                    $lines[] = '> ' . $this->convertHtmlToMarkdown($data['quote']);
                    $lines[] = '';
                }

                if (! empty($data['values']) && \is_array($data['values'])) {
                    foreach ($data['values'] as $value) {
                        if (! \is_array($value)) {
                            continue;
                        }

                        if (empty($value['title'])) {
                            continue;
                        }

                        if (! \is_string($value['title'])) {
                            continue;
                        }

                        $prefix = $this->getListPrefix($value['number'] ?? null);
                        $lines[] = "{$prefix}**{$value['title']}**";
                        if (! empty($value['description']) && \is_string($value['description'])) {
                            $lines[] = '  ' . $value['description'];
                        }

                        $lines[] = '';
                    }
                }

                break;

            case 'value_items':
            case 'journey_steps':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }

                if (! empty($data['subtitle']) && \is_string($data['subtitle'])) {
                    $lines[] = $data['subtitle'];
                    $lines[] = '';
                }

                $items = $data['items'] ?? $data['steps'] ?? [];
                if (! empty($items) && \is_array($items)) {
                    foreach ($items as $item) {
                        if (! \is_array($item)) {
                            continue;
                        }

                        if (empty($item['title'])) {
                            continue;
                        }

                        if (! \is_string($item['title'])) {
                            continue;
                        }

                        $prefix = $this->getListPrefix($item['number'] ?? null);
                        $lines[] = "{$prefix}**{$item['title']}**";

                        if (! empty($item['description']) && \is_string($item['description'])) {
                            $lines[] = '  ' . $item['description'];
                        }

                        $lines[] = '';
                    }
                }

                break;

            case 'moderator':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['name']) && \is_string($data['name'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['name']);
                    $lines[] = '';
                }

                if (! empty($data['bio']) && \is_string($data['bio'])) {
                    $lines[] = $this->convertHtmlToMarkdown($data['bio']);
                    $lines[] = '';
                }

                if (! empty($data['quote']) && \is_string($data['quote'])) {
                    $lines[] = '> ' . $data['quote'];
                    $lines[] = '';
                }

                break;

            case 'cta':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }

                if (! empty($data['text']) && \is_string($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }

                $hasButtonText = ! empty($data['button_text']) && \is_string($data['button_text']);
                $hasButtonLink = ! empty($data['button_link']) && \is_string($data['button_link']);

                if ($hasButtonText && $hasButtonLink) {
                    \assert(\is_string($data['button_text']));
                    \assert(\is_string($data['button_link']));
                    $lines[] = \sprintf('**Aktion:** [%s](%s)', $data['button_text'], $data['button_link']);
                    $lines[] = '';
                }

                break;

            case 'newsletter':
                if (! empty($data['eyebrow']) && \is_string($data['eyebrow'])) {
                    $lines[] = "*{$data['eyebrow']}*";
                    $lines[] = '';
                }

                if (! empty($data['title']) && \is_string($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }

                if (! empty($data['text']) && \is_string($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }

                $lines[] = '*Besucher koennen sich hier fuer den Newsletter anmelden.*';
                $lines[] = '';
                break;

            case 'whatsapp_community':
                $lines[] = '#### WhatsApp Community';
                $lines[] = '';
                $lines[] = 'Tritt unserer WhatsApp Community bei und vernetze dich mit anderen Maennern aus der Region.';
                $lines[] = '';
                if ($this->settings->whatsapp_community_link ?? false) {
                    $lines[] = '**Link zur Community:** ' . $this->settings->whatsapp_community_link;
                    $lines[] = '';
                }

                break;

            case 'testimonials':
                $lines[] = '#### Erfahrungsberichte';
                $lines[] = '';
                $lines[] = '*Dieser Bereich zeigt automatisch veroeffentlichte Erfahrungsberichte von Teilnehmern.*';
                $lines[] = '';
                break;

            case 'faq':
                // FAQ blocks are handled separately in extractFaqBlocks()
                break;
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractFaqBlocks(): array
    {
        $faqBlocks = [];

        $pages = Page::with('contentBlocks')
            ->published()
            ->get();

        foreach ($pages as $page) {
            foreach ($page->contentBlocks as $block) {
                if ($block->type === 'faq' && ! empty($block->data['items'])) {
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

        // Remove script and style tags completely
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        // Convert common HTML tags to Markdown
        $patterns = [
            // Headers
            '/<h1[^>]*>(.*?)<\/h1>/is' => '# $1',
            '/<h2[^>]*>(.*?)<\/h2>/is' => '## $1',
            '/<h3[^>]*>(.*?)<\/h3>/is' => '### $1',
            '/<h4[^>]*>(.*?)<\/h4>/is' => '#### $1',
            '/<h5[^>]*>(.*?)<\/h5>/is' => '##### $1',
            '/<h6[^>]*>(.*?)<\/h6>/is' => '###### $1',

            // Strong/Bold
            '/<strong[^>]*>(.*?)<\/strong>/is' => '**$1**',
            '/<b[^>]*>(.*?)<\/b>/is' => '**$1**',

            // Emphasis/Italic
            '/<em[^>]*>(.*?)<\/em>/is' => '*$1*',
            '/<i[^>]*>(.*?)<\/i>/is' => '*$1*',

            // Links
            '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is' => '[$2]($1)',

            // Line breaks
            '/<br[^>]*>/i' => "\n",
            '/<br\s*\/?>/i' => "\n",

            // Paragraphs
            '/<p[^>]*>(.*?)<\/p>/is' => '$1' . "\n\n",

            // Lists
            '/<ul[^>]*>(.*?)<\/ul>/is' => '$1',
            '/<ol[^>]*>(.*?)<\/ol>/is' => '$1',
            '/<li[^>]*>(.*?)<\/li>/is' => '- $1' . "\n",

            // Blockquotes
            '/<blockquote[^>]*>(.*?)<\/blockquote>/is' => '> $1',

            // Code
            '/<code[^>]*>(.*?)<\/code>/is' => '`$1`',
            '/<pre[^>]*>(.*?)<\/pre>/is' => '```' . "\n" . '$1' . "\n" . '```',

            // Horizontal rule
            '/<hr[^>]*>/i' => '---',

            // Spans with special formatting (preserve the content)
            '/<span[^>]*class=["\']light["\'][^>]*>(.*?)<\/span>/is' => '$1',
        ];

        $markdown = $html;
        foreach ($patterns as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown) ?? $markdown;
        }

        // Remove remaining HTML tags
        $markdown = strip_tags($markdown);

        // Clean up multiple newlines
        $markdown = preg_replace("/\n{3,}/", "\n\n", $markdown) ?? $markdown;

        // Decode HTML entities
        $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim whitespace
        return trim($markdown);
    }

    /**
     * Get list item prefix based on number value
     *
     * @return string
     */
    private function getListPrefix(mixed $number): string
    {
        if (empty($number) || ! \is_scalar($number)) {
            return '- ';
        }

        return (string) $number . '. ';
    }
}
