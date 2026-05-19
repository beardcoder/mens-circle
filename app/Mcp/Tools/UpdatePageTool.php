<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdatePageTool extends Tool
{
    protected string $description = 'Update a CMS page. Provide the page ID and only the fields you want to change.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required()->description('The numeric page ID to update.'),
            'title' => $schema->string()->description('New page title.'),
            'is_published' => $schema->boolean()->description('Whether the page should be published.'),
            'meta' => $schema->object()->description('Page meta data (e.g. description, og:image, etc.).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'title' => ['sometimes', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
            'meta' => ['sometimes', 'array'],
        ]);

        $page = Page::query()->find($data['id']);

        if ($page === null) {
            return Response::error("Page [{$data['id']}] not found.");
        }

        $fields = ['title', 'is_published', 'meta'];

        $updates = array_filter(
            array_intersect_key($data, array_flip($fields)),
            static fn($v): bool => $v !== null,
        );

        if (isset($updates['is_published']) && $updates['is_published'] && $page->published_at === null) {
            $updates['published_at'] = now();
        }

        $page->update($updates);

        return Response::text("Page [{$page->title}] updated successfully.");
    }
}
