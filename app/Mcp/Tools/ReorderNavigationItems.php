<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
        $validated = Validator::make($input, [
            'navigation_id' => ['required', 'uuid', 'exists:navigations,id'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'uuid', 'distinct'],
        ])->validate();

        $navigation = Navigation::findOrFail($validated['navigation_id']);
        $itemIds = $validated['item_ids'];

        $validIds = $navigation->items()
            ->whereIn('id', $itemIds)
            ->pluck('id')
            ->all();
        $invalidIds = array_values(array_diff($itemIds, $validIds));

        if ($invalidIds !== []) {
            throw ValidationException::withMessages([
                'item_ids' => ['The following item IDs are missing or do not belong to the selected navigation: ' . implode(', ', $invalidIds)],
            ]);
        }

        DB::transaction(static function () use ($navigation, $itemIds): void {
            foreach ($itemIds as $order => $itemId) {
                $navigation->items()->where('id', $itemId)->update(['order' => $order]);
            }
        });

        return [
            'success' => true,
            'message' => count($itemIds) . ' items reordered',
        ];
    }
}
