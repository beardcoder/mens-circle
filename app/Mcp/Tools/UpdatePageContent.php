<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description(
    'Replace the content blocks of a page. Accepts the full ordered list of blocks; blocks omitted from the list are deleted. Each block needs a type and a data object; block_id is preserved across edits or generated on insert.',
)]
class UpdatePageContent extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $slug */
        $slug = $request->get('slug');

        /** @var array<int, array{type: string, data?: array<string, mixed>, block_id?: string}> $blocks */
        $blocks = $request->get('blocks');

        $page = Page::query()->where('slug', $slug)->first();

        if (!$page instanceof Page) {
            return Response::error("Page with slug \"{$slug}\" not found.");
        }

        $normalised = array_map(static function (array $block): array {
            $data = $block['data'] ?? [];
            $blockId = $block['block_id'] ?? null;

            if ($blockId !== null) {
                $data['block_id'] = $blockId;
            }

            return [
                'type' => $block['type'],
                'data' => $data,
            ];
        }, $blocks);

        $page->saveContentBlocks($normalised);

        return Response::text(\sprintf('Updated %d content blocks on page "%s".', \count($blocks), $page->slug));
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug of the page to update.')->required(),
            'blocks' => $schema
                ->array()
                ->description('Ordered list of content blocks. Replaces the existing blocks on the page in full.')
                ->items($schema->object([
                    'type' => $schema
                        ->string()
                        ->description(
                            'Block type. One of: hero, intro, text_section, value_items, archetypes, moderator, journey_steps, testimonials, faq, newsletter, cta, whatsapp_community.',
                        )
                        ->required(),
                    'block_id' => $schema->string()->description('Optional UUID preserved across edits; auto-generated when omitted.'),
                    'data' => $schema->object()->description('Block-specific payload. See get-page output for shape per type.'),
                ]))
                ->required(),
        ];
    }
}
