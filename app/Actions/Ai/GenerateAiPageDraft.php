<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Page;
use App\Services\Ai\AiAuditLogger;
use Illuminate\Support\Str;

final readonly class GenerateAiPageDraft
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Page
    {
        $title = (string) $data['title'];
        $prompt = (string) $data['prompt'];

        $page = Page::create([
            'title' => $title,
            'slug' => $data['slug'] ?? Str::slug($title),
            'meta' => $data['meta'] ?? ['description' => Str::limit(strip_tags($prompt), 160)],
            'is_published' => false,
            'published_at' => null,
        ]);

        $page->saveContentBlocks($this->generateContentBlocks($title, $prompt));

        $this->auditLogger->log('ai.page.generated', [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ]);

        return $page->fresh('contentBlocks');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function generateContentBlocks(string $title, string $prompt): array
    {
        return [
            [
                'type' => 'hero',
                'data' => [
                    'block_id' => (string) Str::uuid(),
                    'label' => 'Neu',
                    'title' => $title,
                    'description' => Str::limit($prompt, 220),
                    'button_text' => 'Mehr erfahren',
                    'button_link' => route('home'),
                ],
            ],
            [
                'type' => 'text_section',
                'data' => [
                    'block_id' => (string) Str::uuid(),
                    'eyebrow' => 'KI-Entwurf',
                    'title' => $title,
                    'content' => '<p>' . e($prompt) . '</p>',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'block_id' => (string) Str::uuid(),
                    'eyebrow' => 'Nächster Schritt',
                    'title' => 'Möchtest du dabei sein?',
                    'text' => 'Nutze diesen Entwurf als Ausgangspunkt und passe ihn im Admin-Bereich weiter an.',
                    'button_text' => 'Zum nächsten Event',
                    'button_link' => route('home'),
                ],
            ],
        ];
    }
}
