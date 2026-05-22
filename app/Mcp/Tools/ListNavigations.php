<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Attributes\CallableName;
use Laravel\Mcp\Server\Attributes\Description;

#[CallableName('list-navigations')]
#[Description('List all navigations with their items')]
class ListNavigations extends Tool
{
    public function __invoke(): array
    {
        $navigations = Navigation::with(['items' => static function ($query): void {
            $query->orderBy('order');
        }])
            ->orderBy('type')
            ->get();

        return [
            'navigations' => $navigations->map(static function (Navigation $navigation): array {
                return [
                    'id' => $navigation->id,
                    'name' => $navigation->name,
                    'type' => $navigation->type->value,
                    'is_active' => $navigation->is_active,
                    'items_count' => $navigation->items->count(),
                    'items' => $navigation->items->map(static function ($item): array {
                        return [
                            'id' => $item->id,
                            'label' => $item->label,
                            'url' => $item->url,
                            'route_name' => $item->route_name,
                            'route_params' => $item->route_params,
                            'anchor' => $item->anchor,
                            'computed_url' => $item->computed_url,
                            'order' => $item->order,
                            'is_active' => $item->is_active,
                            'parent_id' => $item->parent_id,
                        ];
                    })->all(),
                ];
            })->all(),
        ];
    }
}
