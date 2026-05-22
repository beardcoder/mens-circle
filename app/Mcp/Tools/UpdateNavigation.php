<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Attributes\CallableName;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Parameter;
use Laravel\Mcp\Server\Contracts\HasInput;

#[CallableName('update-navigation')]
#[Description('Update navigation items. Replaces all items with the provided list.')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
#[Parameter('items', 'array', 'Array of navigation items with label, url, route_name, anchor, etc.')]
class UpdateNavigation extends Tool implements HasInput
{
    /**
     * @param array{navigation_id: string, items: array<int, array{label: string, url?: ?string, route_name?: ?string, route_params?: ?array, anchor?: ?string, target?: ?string, is_active?: bool, icon?: ?string, css_class?: ?string, data_attributes?: ?array, parent_id?: ?string}>} $input
     */
    public function __invoke(array $input): array
    {
        $navigation = Navigation::findOrFail($input['navigation_id']);

        DB::transaction(static function () use ($navigation, $input): void {
            // Delete all existing items
            $navigation->items()->delete();

            // Create new items
            foreach ($input['items'] as $order => $itemData) {
                NavigationItem::create([
                    'navigation_id' => $navigation->id,
                    'parent_id' => $itemData['parent_id'] ?? null,
                    'label' => $itemData['label'],
                    'url' => $itemData['url'] ?? null,
                    'route_name' => $itemData['route_name'] ?? null,
                    'route_params' => $itemData['route_params'] ?? null,
                    'anchor' => $itemData['anchor'] ?? null,
                    'target' => $itemData['target'] ?? '_self',
                    'order' => $order,
                    'is_active' => $itemData['is_active'] ?? true,
                    'icon' => $itemData['icon'] ?? null,
                    'css_class' => $itemData['css_class'] ?? null,
                    'data_attributes' => $itemData['data_attributes'] ?? null,
                ]);
            }
        });

        $navigation->refresh();
        $navigation->load('items');

        return [
            'success' => true,
            'message' => "Navigation '{$navigation->name}' updated with " . count($input['items']) . ' items',
            'items_count' => $navigation->items->count(),
        ];
    }
}
