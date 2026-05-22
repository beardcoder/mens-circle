<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description('Update a navigation item by id. Only provided fields are changed. Pass condition="" (empty string) to clear the condition.')]
class UpdateNavigationItem extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var int|string $id */
        $id = $request->get('id');

        $item = NavigationItem::query()->find($id);

        if (!$item instanceof NavigationItem) {
            return Response::error("Navigation item with id \"{$id}\" not found.");
        }

        if (($location = $request->get('location')) !== null) {
            $locationEnum = NavigationLocation::tryFrom((string) $location);

            if ($locationEnum === null) {
                return Response::error("Unknown location \"{$location}\".");
            }

            $item->location = $locationEnum;
        }

        if (($condition = $request->get('condition')) !== null) {
            if ($condition === '') {
                $item->condition = null;
            } else {
                $conditionEnum = NavigationCondition::tryFrom((string) $condition);

                if ($conditionEnum === null) {
                    return Response::error("Unknown condition \"{$condition}\".");
                }

                $item->condition = $conditionEnum;
            }
        }

        foreach (['label', 'url', 'umami_event_target'] as $field) {
            $value = $request->get($field);

            if ($value !== null) {
                $item->{$field} = (string) $value;
            }
        }

        foreach (['open_in_new_tab', 'is_cta', 'is_visible'] as $boolField) {
            $value = $request->get($boolField);

            if ($value !== null) {
                $item->{$boolField} = (bool) $value;
            }
        }

        if (($sort = $request->get('sort')) !== null) {
            $item->sort = (int) $sort;
        }

        $item->save();

        return Response::text("Navigation item #{$item->id} updated.");
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the navigation item to update.')->required(),
            'location' => $schema->string()->description('Navigation area: header, footer_primary, footer_contact, footer_legal.'),
            'label' => $schema->string()->description('Visible link text.'),
            'url' => $schema->string()->description('Target URL/path/anchor.'),
            'condition' => $schema->string()->description('Dynamic condition or empty string to clear.'),
            'open_in_new_tab' => $schema->boolean()->description('Open link in new tab.'),
            'is_cta' => $schema->boolean()->description('Render as primary CTA button.'),
            'is_visible' => $schema->boolean()->description('Whether the item is rendered.'),
            'umami_event_target' => $schema->string()->description('Value for data-umami-event-target.'),
            'sort' => $schema->integer()->description('Sort order within the location.'),
        ];
    }
}
