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

#[Description('List CMS pages with id, slug, title, publish state and content block summary.')]
class ListPages extends Tool
{
    public function handle(Request $request): Response
    {
        $pages = Page::query()
            ->withCount('contentBlocks')
            ->orderBy('title')
            ->get(['id', 'slug', 'title', 'is_published', 'published_at', 'updated_at']);

        return Response::json(
            $pages->map(static fn(Page $page): array => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'is_published' => $page->is_published,
                'published_at' => $page->published_at?->toIso8601String(),
                'updated_at' => $page->updated_at?->toIso8601String(),
                'content_blocks_count' => $page->content_blocks_count,
            ])->all(),
        );
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
