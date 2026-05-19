<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetPageTool extends Tool
{
    protected string $description = 'Get a CMS page by ID or slug, including its content blocks.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric page ID.'),
            'slug' => $schema->string()->description('The page slug (alternative to ID).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if ($id === null && $slug === null) {
            return Response::error('Provide either "id" or "slug".');
        }

        $query = Page::query()->with('contentBlocks');

        $page = $id !== null
            ? $query->find($id)
            : $query->where('slug', $slug)->first();

        if ($page === null) {
            return Response::error('Page not found.');
        }

        return Response::json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'meta' => $page->meta,
            'is_published' => $page->is_published,
            'published_at' => $page->published_at?->toISOString(),
            'content_blocks' => $page->contentBlocks->map(static fn($block): array => [
                'id' => $block->id,
                'block_id' => $block->block_id,
                'type' => $block->type,
                'order' => $block->order,
                'data' => $block->data,
            ])->all(),
        ]);
    }
}
