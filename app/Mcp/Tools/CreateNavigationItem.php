<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use App\Models\NavigationItem;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Attributes\CallableName;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Parameter;
use Laravel\Mcp\Server\Contracts\HasInput;

#[CallableName('create-navigation-item')]
#[Description('Add a new item to a navigation')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
#[Parameter('label', 'string', 'The display label')]
#[Parameter('url', 'string|null', 'Direct URL (optional if route_name provided)')]
#[Parameter('route_name', 'string|null', 'Laravel route name (optional if url provided)')]
#[Parameter('route_params', 'array|null', 'Route parameters as key-value pairs')]
#[Parameter('anchor', 'string|null', 'Anchor/hash for the link (e.g., "faq" for #faq)')]
#[Parameter('target', 'string|null', 'Link target (_self or _blank)')]
#[Parameter('parent_id', 'string|null', 'Parent item ID for nested navigation')]
#[Parameter('is_active', 'boolean|null', 'Whether item is active (default: true)')]
#[Parameter('icon', 'string|null', 'Icon class or name')]
#[Parameter('css_class', 'string|null', 'Additional CSS classes')]
#[Parameter('data_attributes', 'array|null', 'Data attributes (e.g., umami-event)')]
class CreateNavigationItem extends Tool implements HasInput
{
    /**
     * @param array{navigation_id: string, label: string, url?: ?string, route_name?: ?string, route_params?: ?array, anchor?: ?string, target?: ?string, parent_id?: ?string, is_active?: ?bool, icon?: ?string, css_class?: ?string, data_attributes?: ?array} $input
     */
    public function __invoke(array $input): array
    {
        $navigation = Navigation::findOrFail($input['navigation_id']);

        // Validate parent_id if provided
        if (!empty($input['parent_id'])) {
            $parent = NavigationItem::find($input['parent_id']);

            if (!$parent) {
                return [
                    'success' => false,
                    'error' => "Parent item with ID '{$input['parent_id']}' not found",
                ];
            }

            if ($parent->navigation_id !== $navigation->id) {
                return [
                    'success' => false,
                    'error' => "Parent item belongs to a different navigation. Parent is in navigation '{$parent->navigation->name}', but you're trying to add to '{$navigation->name}'",
                ];
            }
        }

        // Validate and restrict target value
        $target = $input['target'] ?? '_self';
        if (!in_array($target, ['_self', '_blank', '_parent', '_top'])) {
            $target = '_self';
        }

        // Get the max order for proper positioning
        $maxOrder = $navigation->items()->max('order') ?? -1;

        $item = NavigationItem::create([
            'navigation_id' => $navigation->id,
            'parent_id' => $input['parent_id'] ?? null,
            'label' => $input['label'],
            'url' => $input['url'] ?? null,
            'route_name' => $input['route_name'] ?? null,
            'route_params' => $input['route_params'] ?? null,
            'anchor' => $input['anchor'] ?? null,
            'target' => $target,
            'order' => $maxOrder + 1,
            'is_active' => $input['is_active'] ?? true,
            'icon' => $input['icon'] ?? null,
            'css_class' => $input['css_class'] ?? null,
            'data_attributes' => $input['data_attributes'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "Navigation item '{$item->label}' created",
            'item' => [
                'id' => $item->id,
                'label' => $item->label,
                'computed_url' => $item->computed_url,
                'order' => $item->order,
            ],
        ];
    }
}
