<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
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
