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

#[CallableName('reorder-navigation-items')]
#[Description('Reorder navigation items by providing an array of item IDs in the desired order')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
#[Parameter('item_ids', 'array', 'Array of item IDs in desired order')]
class ReorderNavigationItems extends Tool implements HasInput
{
    /**
     * @param array{navigation_id: string, item_ids: array<int, string>} $input
     */
    public function __invoke(array $input): array
    {
        $navigation = Navigation::findOrFail($input['navigation_id']);

        // Validate all item IDs exist and belong to this navigation
        $itemIds = $input['item_ids'];
        $items = NavigationItem::whereIn('id', $itemIds)->get();

        if ($items->count() !== count($itemIds)) {
            $foundIds = $items->pluck('id')->toArray();
            $missingIds = array_diff($itemIds, $foundIds);

            return [
                'success' => false,
                'error' => 'Some item IDs were not found: ' . implode(', ', $missingIds),
            ];
        }

        // Check all items belong to the specified navigation
        $foreignItems = $items->filter(fn($item) => $item->navigation_id !== $navigation->id);

        if ($foreignItems->isNotEmpty()) {
            $foreignIds = $foreignItems->pluck('id')->toArray();

            return [
                'success' => false,
                'error' => 'Some items belong to a different navigation: ' . implode(', ', $foreignIds),
            ];
        }

        // All validations passed, proceed with reordering
        DB::transaction(static function () use ($navigation, $input): void {
            foreach ($input['item_ids'] as $order => $itemId) {
                $navigation->items()->where('id', $itemId)->update(['order' => $order]);
            }
        });

        return [
            'success' => true,
            'message' => count($input['item_ids']) . ' items reordered',
        ];
    }
}
