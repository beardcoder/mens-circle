<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Newsletter;
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
        $siteTagline = $settings->site_tagline ?? '';

        $lines = [];

        // Header with Metadata
        $lines[] = '# ' . $siteName;
        $lines[] = '';

        if ($siteTagline) {
            $lines[] = '> ' . $siteTagline;
            $lines[] = '';
        }

        $lines[] = '**Beschreibung:** ' . $siteDescription;
        $lines[] = '';
        $lines[] = '**Letzte Aktualisierung:** ' . now()->format('d.m.Y H:i') . ' Uhr';
        $lines[] = '**Website:** ' . url('/');

        if ($settings->contact_email ?? false) {
            $lines[] = '**Kontakt E-Mail:** ' . $settings->contact_email;
        }

        if ($settings->contact_phone ?? false) {
            $lines[] = '**Telefon:** ' . $settings->contact_phone;
        }

        if ($settings->location ?? false) {
            $lines[] = '**Standort:** ' . $settings->location;
        }

        if ($settings->whatsapp_community_link ?? false) {
            $lines[] = '**WhatsApp Community:** ' . $settings->whatsapp_community_link;
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        // About / Organization Context
        $lines[] = '## Über ' . $siteName;
        $lines[] = '';
        $lines[] = 'Der Männerkreis Niederbayern ist eine Gemeinschaft für Männer, die sich regelmäßig zu Veranstaltungen treffen. Die Website bietet Informationen zu kommenden Events, Anmeldung zu Veranstaltungen und einen Newsletter-Service.';
        $lines[] = '';

        if ($settings->footer_text ?? false) {
            $lines[] = strip_tags($settings->footer_text);
            $lines[] = '';
        }

        // Social Media Links
        if (!empty($settings->social_links)) {
            $lines[] = '### Social Media';
            $lines[] = '';
            foreach ($settings->social_links as $platform => $data) {
                /** @var array<string, mixed> $data */
                $url = $data['url'] ?? $data['link'] ?? null;
                $platformName = $data['platform'] ?? $data['name'] ?? ucfirst((string) $platform);

                if ($url) {
                    $lines[] = '- **' . $platformName . ':** ' . $url;
                }
            }
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '';

        // Statistics Section
        $lines[] = '## Statistiken';
        $lines[] = '';

        $totalEvents = Event::published()->count();
        $upcomingEventsCount = Event::published()->upcoming()->count();
        $pastEventsCount = Event::published()->where('event_date', '<', now())->count();
        $totalTestimonials = Testimonial::published()->count();
        $totalNewsletterSubscribers = Newsletter::where('status', 'active')->count();

        $lines[] = '- **Gesamtanzahl Veranstaltungen:** ' . $totalEvents;
        $lines[] = '- **Kommende Veranstaltungen:** ' . $upcomingEventsCount;
        $lines[] = '- **Vergangene Veranstaltungen:** ' . $pastEventsCount;

        if ($totalTestimonials > 0) {
            $lines[] = '- **Veröffentlichte Erfahrungsberichte:** ' . $totalTestimonials;
        }

        if ($totalNewsletterSubscribers > 0) {
            $lines[] = '- **Newsletter-Abonnenten:** ' . $totalNewsletterSubscribers;
        }

        $lines[] = '';
        $lines[] = '---';
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
                $lines[] = '**Uhrzeit:** ' . $event->start_time->format('H:i') . ' - ' . $event->end_time->format('H:i') . ' Uhr';

                if ($event->location) {
                    $lines[] = '**Ort:** ' . $event->location;
                }

                if ($event->fullAddress) {
                    $lines[] = '**Adresse:** ' . $event->fullAddress;
                }

                $lines[] = '**Verfügbare Plätze:** ' . $event->availableSpots . ' von ' . $event->max_participants;

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
        }

        // Past Events Section
        $pastEvents = Event::published()
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->limit(5)
            ->get();

        if ($pastEvents->isNotEmpty()) {
            $lines[] = '## Vergangene Veranstaltungen (letzte 5)';
            $lines[] = '';
            foreach ($pastEvents as $event) {
                $date = $event->event_date->format('d.m.Y');
                $url = url()->route('event.show.slug', $event->slug);
                $lines[] = sprintf('- **[%s](%s)** - %s', $event->title, $url, $date);
            }
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
        }

        // Pages with Content Section
        $lines[] = '## Seiteninhalte';
        $lines[] = '';
        $lines[] = '### Startseite';
        $lines[] = '';
        $lines[] = 'Die Hauptseite mit einem Überblick über den Männerkreis Niederbayern.';
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
                        $lines[] = '### ' . $this->convertHtmlToMarkdown($faq['title']);
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
                            $lines[] = '**A:** ' . $this->convertHtmlToMarkdown($item['answer']);
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
        $lines[] = '- **[Impressum](' . url()->route('page.show', 'impressum') . '):** Rechtliche Angaben und Anbieterkennzeichnung';
        $lines[] = '- **[Datenschutz](' . url()->route('page.show', 'datenschutz') . '):** Datenschutzerklärung gemäß DSGVO';
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        // Actions Section
        $lines[] = '## Verfügbare Aktionen auf der Website';
        $lines[] = '';
        $lines[] = '1. **Newsletter-Anmeldung**';
        $lines[] = '   - Über das Formular auf der Startseite können sich Interessierte für den Newsletter anmelden';
        $lines[] = '   - Regelmäßige Updates zu neuen Veranstaltungen und Neuigkeiten';
        $lines[] = '';
        $lines[] = '2. **Veranstaltungsanmeldung**';
        $lines[] = '   - Über die jeweilige Veranstaltungsseite können sich Teilnehmer direkt registrieren';
        $lines[] = '   - Online-Formular mit persönlichen Daten und optionalen Notizen';
        $lines[] = '';
        $lines[] = '3. **Erfahrungsbericht teilen**';
        $lines[] = '   - Teilnehmer können ihre Erfahrungen über ein Formular einreichen';
        $lines[] = '   - Nach Prüfung werden Erfahrungsberichte auf der Website veröffentlicht';
        $lines[] = '';

        if ($settings->whatsapp_community_link ?? false) {
            $lines[] = '4. **WhatsApp Community beitreten**';
            $lines[] = '   - Direkter Austausch mit anderen Teilnehmern';
            $lines[] = '   - Link: ' . $settings->whatsapp_community_link;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Format a content block for the llms.txt output
     *
     * @param \App\Models\ContentBlock $block
     * @return array<int, string>
     */
    private function formatContentBlock($block): array
    {
        $lines = [];
        $data = $block->data;

        switch ($block->type) {
            case 'hero':
                if (!empty($data['label'])) {
                    $lines[] = '*' . $data['label'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['description'])) {
                    $lines[] = $data['description'];
                    $lines[] = '';
                }
                if (!empty($data['button_text']) && !empty($data['button_link'])) {
                    $lines[] = '**Call-to-Action:** [' . $data['button_text'] . '](' . $data['button_link'] . ')';
                    $lines[] = '';
                }
                break;

            case 'text_section':
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $data['title'];
                    $lines[] = '';
                }
                if (!empty($data['content'])) {
                    $lines[] = $this->convertHtmlToMarkdown($data['content']);
                    $lines[] = '';
                }
                break;

            case 'intro':
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }
                if (!empty($data['quote'])) {
                    $lines[] = '> ' . $this->convertHtmlToMarkdown($data['quote']);
                    $lines[] = '';
                }
                if (!empty($data['values'])) {
                    foreach ($data['values'] as $value) {
                        if (!empty($value['title'])) {
                            $prefix = !empty($value['number']) ? $value['number'] . '. ' : '- ';
                            $lines[] = $prefix . '**' . $value['title'] . '**';
                            if (!empty($value['description'])) {
                                $lines[] = '  ' . $value['description'];
                            }
                            $lines[] = '';
                        }
                    }
                }
                break;

            case 'value_items':
            case 'journey_steps':
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['subtitle'])) {
                    $lines[] = $data['subtitle'];
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
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['name'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['name']);
                    $lines[] = '';
                }
                if (!empty($data['bio'])) {
                    $lines[] = $this->convertHtmlToMarkdown($data['bio']);
                    $lines[] = '';
                }
                if (!empty($data['quote'])) {
                    $lines[] = '> ' . $data['quote'];
                    $lines[] = '';
                }
                break;

            case 'cta':
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }
                if (!empty($data['button_text']) && !empty($data['button_link'])) {
                    $lines[] = '**Aktion:** [' . $data['button_text'] . '](' . $data['button_link'] . ')';
                    $lines[] = '';
                }
                break;

            case 'newsletter':
                if (!empty($data['eyebrow'])) {
                    $lines[] = '*' . $data['eyebrow'] . '*';
                    $lines[] = '';
                }
                if (!empty($data['title'])) {
                    $lines[] = '#### ' . $this->convertHtmlToMarkdown($data['title']);
                    $lines[] = '';
                }
                if (!empty($data['text'])) {
                    $lines[] = $data['text'];
                    $lines[] = '';
                }
                $lines[] = '*Besucher können sich hier für den Newsletter anmelden.*';
                $lines[] = '';
                break;

            case 'whatsapp_community':
                $settings = app(GeneralSettings::class);
                $lines[] = '#### WhatsApp Community';
                $lines[] = '';
                $lines[] = 'Tritt unserer WhatsApp Community bei und vernetze dich mit anderen Männern aus der Region.';
                $lines[] = '';
                if ($settings->whatsapp_community_link ?? false) {
                    $lines[] = '**Link zur Community:** ' . $settings->whatsapp_community_link;
                    $lines[] = '';
                }
                break;

            case 'testimonials':
                $lines[] = '#### Erfahrungsberichte';
                $lines[] = '';
                $lines[] = '*Dieser Bereich zeigt automatisch veröffentlichte Erfahrungsberichte von Teilnehmern.*';
                $lines[] = '';
                break;

            case 'faq':
                // FAQ blocks are handled separately in extractFaqBlocks()
                break;
        }

        return $lines;
    }

    /**
     * Extract FAQ blocks from all published pages
     *
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
                if ($block->type === 'faq' && !empty($block->data['items'])) {
                    $faqBlocks[] = $block->data;
                }
            }
        }

        return $faqBlocks;
    }

    /**
     * Convert HTML to Markdown
     *
     * @param string $html
     * @return string
     */
    private function convertHtmlToMarkdown(string $html): string
    {
        if (empty($html)) {
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
        $markdown = trim($markdown);

        return $markdown;
    }
}
