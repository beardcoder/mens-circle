<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description('Delete a navigation item by id.')]
final class DeleteNavigationItem extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var int|string $id */
        $id = $request->get('id');

        $item = NavigationItem::query()->find($id);

        if (!$item instanceof NavigationItem) {
            return Response::error("Navigation item with id \"{$id}\" not found.");
        }

        $item->delete();

        return Response::text("Navigation item #{$id} deleted.");
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the navigation item to delete.')->required(),
        ];
    }
}
