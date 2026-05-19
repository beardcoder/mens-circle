<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListPagesTool extends Tool
{
    protected string $description = 'List all CMS pages with their title, slug and published status.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response
    {
        $pages = Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'is_published', 'published_at', 'created_at', 'updated_at']);

        return Response::json(
            $pages->map(static fn(Page $page): array => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'is_published' => $page->is_published,
                'published_at' => $page->published_at?->toISOString(),
                'updated_at' => $page->updated_at?->toISOString(),
            ])->all(),
        );
    }
}

