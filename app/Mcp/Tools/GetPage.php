<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\ContentBlock;
use App\Models\Page;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description(
    'Fetch a single CMS page by slug, including title, SEO meta and ordered content blocks (with block_id, type, data). Block anchors live inside data["anchor"] and can be linked from NavigationItem.anchor.',
)]
final class GetPage extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $slug */
        $slug = $request->get('slug');

        $page = Page::query()->with('contentBlocks')->where('slug', $slug)->first();

        if (!$page instanceof Page) {
            return Response::error("Page with slug \"{$slug}\" not found.");
        }

        return Response::json([
            'id' => $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'is_published' => $page->is_published,
            'published_at' => $page->published_at?->toIso8601String(),
            'meta' => $page->meta,
            'content_blocks' => $page->contentBlocks->map(static fn(ContentBlock $block): array => [
                'block_id' => $block->block_id,
                'type' => $block->type,
                'order' => $block->order,
                'anchor' => $block->data['anchor'] ?? null,
                'data' => $block->data,
            ])->all(),
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The unique slug of the page to fetch (e.g. "home", "impressum").')->required(),
        ];
    }
}
