<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Attributes\CallableName;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Parameter;
use Laravel\Mcp\Server\Contracts\HasInput;

#[CallableName('get-navigation')]
#[Description('Get a specific navigation by ID with all its items')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
class GetNavigation extends Tool implements HasInput
{
    /**
     * @param array{navigation_id: string} $input
     */
    public function __invoke(array $input): array
    {
        $navigation = Navigation::with(['items' => static function ($query): void {
            $query->orderBy('order');
        }])->findOrFail($input['navigation_id']);

        return [
            'id' => $navigation->id,
            'name' => $navigation->name,
            'type' => $navigation->type->value,
            'is_active' => $navigation->is_active,
            'items' => $navigation->items->map(static function ($item): array {
                return [
                    'id' => $item->id,
                    'parent_id' => $item->parent_id,
                    'label' => $item->label,
                    'url' => $item->url,
                    'route_name' => $item->route_name,
                    'route_params' => $item->route_params,
                    'anchor' => $item->anchor,
                    'target' => $item->target,
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                    'icon' => $item->icon,
                    'css_class' => $item->css_class,
                    'data_attributes' => $item->data_attributes,
                    'computed_url' => $item->computed_url,
                ];
            })->all(),
        ];
    }
}
