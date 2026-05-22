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
#[Description('Update navigation items. Replaces all items with the provided list. For nested items, use temp_id and parent_temp_id to define relationships.')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
#[Parameter('items', 'array', 'Array of navigation items with label, url, route_name, anchor, etc. Use temp_id and parent_temp_id for nesting.')]
class UpdateNavigation extends Tool implements HasInput
{
    /**
     * @param array{navigation_id: string, items: array<int, array{temp_id?: ?string, parent_temp_id?: ?string, label: string, url?: ?string, route_name?: ?string, route_params?: ?array, anchor?: ?string, target?: ?string, is_active?: bool, icon?: ?string, css_class?: ?string, data_attributes?: ?array}>} $input
     */
    public function __invoke(array $input): array
    {
        $navigation = Navigation::findOrFail($input['navigation_id']);

        DB::transaction(function () use ($navigation, $input): void {
            // Delete all existing items
            $navigation->items()->delete();

            // First pass: Create items without parent relationships, build ID map
            $idMap = [];
            $itemsToCreate = [];

            foreach ($input['items'] as $order => $itemData) {
                $tempId = $itemData['temp_id'] ?? null;
                $itemsToCreate[$order] = [
                    'temp_id' => $tempId,
                    'parent_temp_id' => $itemData['parent_temp_id'] ?? null,
                    'data' => [
                        'navigation_id' => $navigation->id,
                        'parent_id' => null, // Set in second pass
                        'label' => $itemData['label'],
                        'url' => $itemData['url'] ?? null,
                        'route_name' => $itemData['route_name'] ?? null,
                        'route_params' => $itemData['route_params'] ?? null,
                        'anchor' => $itemData['anchor'] ?? null,
                        'target' => in_array($itemData['target'] ?? '_self', ['_self', '_blank', '_parent', '_top'])
                            ? ($itemData['target'] ?? '_self')
                            : '_self',
                        'order' => $order,
                        'is_active' => $itemData['is_active'] ?? true,
                        'icon' => $itemData['icon'] ?? null,
                        'css_class' => $itemData['css_class'] ?? null,
                        'data_attributes' => $itemData['data_attributes'] ?? null,
                    ],
                ];
            }

            // Create items and build temp_id -> real UUID map
            foreach ($itemsToCreate as $order => $itemInfo) {
                $item = NavigationItem::create($itemInfo['data']);
                if ($itemInfo['temp_id']) {
                    $idMap[$itemInfo['temp_id']] = $item->id;
                }
            }

            // Second pass: Update parent_id for nested items
            foreach ($itemsToCreate as $order => $itemInfo) {
                if ($itemInfo['parent_temp_id'] && isset($idMap[$itemInfo['parent_temp_id']])) {
                    $realParentId = $idMap[$itemInfo['parent_temp_id']];
                    $tempId = $itemInfo['temp_id'] ?? null;
                    $realItemId = $tempId && isset($idMap[$tempId]) ? $idMap[$tempId] : null;

                    if ($realItemId) {
                        NavigationItem::where('id', $realItemId)->update(['parent_id' => $realParentId]);
                    }
                }
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
