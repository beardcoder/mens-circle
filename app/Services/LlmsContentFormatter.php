<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentBlock;
use App\Settings\GeneralSettings;

/**
 * Renders Markdown for the llms.txt output from CMS content blocks and HTML snippets.
 */
final readonly class LlmsContentFormatter
{
    public function __construct(
        private GeneralSettings $settings,
    ) {}

    /**
     * @return array<int, string>
     */
    public function formatContentBlock(ContentBlock $block): array
    {
        /** @var array<string, mixed> $data */
        $data = $block->data;

        return match ($block->type) {
            'hero' => $this->formatHeroBlock($data),
            'page_hero' => $this->formatPageHeroBlock($data),
            'text_section' => $this->formatTextSectionBlock($data),
            'intro' => $this->formatIntroBlock($data),
            'value_items', 'archetypes', 'journey_steps' => $this->formatItemsBlock($data),
            'moderator' => $this->formatModeratorBlock($data),
            'cta' => $this->formatCtaBlock($data),
            'newsletter' => $this->formatNewsletterBlock($data),
            'whatsapp_community' => $this->formatWhatsappBlock(),
            'testimonials' => [
                '#### Erfahrungsberichte',
                '',
                '*Dieser Bereich zeigt automatisch veroeffentlichte Erfahrungsberichte von Teilnehmern.*',
                '',
            ],
            default => [],
        };
    }

    public function convertHtmlToMarkdown(string $html): string
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
    public function stringField(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
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
    private function formatPageHeroBlock(array $data): array
    {
        $lines = $this->appendEyebrow([], $data, 'eyebrow');
        $lines = $this->appendHeading($lines, $data, 'title');
        $lines = $this->appendParagraph($lines, $data, 'lead');

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

        $title = $this->stringField($data, 'title');
        if ($title !== null) {
            $lines[] = '#### ' . $title;
            $lines[] = '';
        }

        $content = $this->stringField($data, 'content');
        if ($content !== null) {
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

        $quote = $this->stringField($data, 'quote');
        if ($quote !== null) {
            $lines[] = '> ' . $this->convertHtmlToMarkdown($quote);
            $lines[] = '';
        }

        $values = $data['values'] ?? null;
        if (\is_array($values)) {
            return $this->appendItemList($lines, $values);
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

        $bio = $this->stringField($data, 'bio');
        if ($bio !== null) {
            $lines[] = $this->convertHtmlToMarkdown($bio);
            $lines[] = '';
        }

        $quote = $this->stringField($data, 'quote');
        if ($quote !== null) {
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
     * @param array<int, string> $lines
     * @param array<array-key, mixed> $data
     *
     * @return array<int, string>
     */
    private function appendEyebrow(array $lines, array $data, string $key): array
    {
        $value = $this->stringField($data, $key);

        if ($value !== null) {
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
        $value = $this->stringField($data, $key);

        if ($value !== null) {
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
        $value = $this->stringField($data, $key);

        if ($value !== null) {
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

        if ($text !== null && $link !== null) {
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
            if ($title === null) {
                continue;
            }

            $number = $item['number'] ?? null;
            $prefix = \is_scalar($number) && !\in_array($number, [null, '', 0, false], true) ? "{$number}. " : '- ';

            $lines[] = "{$prefix}**{$title}**";

            $description = $this->stringField($item, 'description');
            if ($description !== null) {
                $lines[] = '  ' . $description;
            }

            $lines[] = '';
        }

        return $lines;
    }
}
