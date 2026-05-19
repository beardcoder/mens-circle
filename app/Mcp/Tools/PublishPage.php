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

#[Description('Toggle publish state of a page. Sets is_published and stamps published_at when publishing.')]
class PublishPage extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $slug */
        $slug = $request->get('slug');

        /** @var bool $publish */
        $publish = $request->get('publish');

        $page = Page::query()->where('slug', $slug)->first();

        if (!$page instanceof Page) {
            return Response::error("Page with slug \"{$slug}\" not found.");
        }

        $page->update([
            'is_published' => $publish,
            'published_at' => $publish ? $page->published_at ?? now() : null,
        ]);

        return Response::text(\sprintf('Page "%s" is now %s.', $page->slug, $publish ? 'published' : 'unpublished'));
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Slug of the page.')->required(),
            'publish' => $schema->boolean()->description('true to publish, false to unpublish.')->required(),
        ];
    }
}
