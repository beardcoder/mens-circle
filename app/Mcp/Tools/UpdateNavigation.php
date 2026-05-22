<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Attributes\CallableName;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Parameter;
use Laravel\Mcp\Server\Contracts\HasInput;

#[CallableName('update-navigation')]
#[Description('Update navigation items. Replaces all items with the provided list. Supports nesting with temp_id/parent_temp_id or id/parent_id references.')]
#[Parameter('navigation_id', 'string', 'The UUID of the navigation')]
#[Parameter('items', 'array', 'Array of navigation items with label, url, route_name, anchor, etc. Use temp_id/parent_temp_id or id/parent_id for nesting. If both are provided, temp_id and parent_temp_id take precedence.')]
class UpdateNavigation extends Tool implements HasInput
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TARGETS = ['_self', '_blank', '_parent', '_top'];

    /**
     * @param array{navigation_id: string, items?: array<int, array{id?: ?string, temp_id?: ?string, parent_id?: ?string, parent_temp_id?: ?string, label: string, url?: ?string, route_name?: ?string, route_params?: ?array, anchor?: ?string, target?: ?string, is_active?: bool, icon?: ?string, css_class?: ?string, data_attributes?: ?array}>} $input
     */
    public function __invoke(array $input): array
    {
        $validated = Validator::make($input, [
            'navigation_id' => ['required', 'uuid', 'exists:navigations,id'],
            'items' => ['sometimes', 'array'],
            'items.*.id' => ['nullable', 'uuid'],
            'items.*.temp_id' => ['nullable', 'string'],
            'items.*.parent_id' => ['nullable', 'uuid'],
            'items.*.parent_temp_id' => ['nullable', 'string'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.route_name' => ['nullable', 'string', 'max:255'],
            'items.*.route_params' => ['nullable', 'array'],
            'items.*.anchor' => ['nullable', 'string', 'max:255'],
            'items.*.target' => ['nullable', Rule::in(self::ALLOWED_TARGETS)],
            'items.*.is_active' => ['nullable', 'boolean'],
            'items.*.icon' => ['nullable', 'string', 'max:255'],
            'items.*.css_class' => ['nullable', 'string', 'max:255'],
            'items.*.data_attributes' => ['nullable', 'array'],
        ])->validate();

        $navigation = Navigation::findOrFail($validated['navigation_id']);
        $items = $validated['items'] ?? [];

        DB::transaction(function () use ($navigation, $items): void {
            $navigation->items()->delete();

            $preparedItems = [];
            $knownReferences = [];

            foreach ($items as $order => $itemData) {
                $itemReference = $this->resolveItemReference($itemData, $order);
                $parentReference = $this->resolveParentReference($itemData);

                if (isset($knownReferences[$itemReference])) {
                    throw ValidationException::withMessages([
                        'items' => ['Duplicate item detected in the provided list.'],
                    ]);
                }

                $knownReferences[$itemReference] = true;
                $preparedItems[] = [
                    'reference' => $itemReference,
                    'parent_reference' => $parentReference,
                    'data' => [
                        'navigation_id' => $navigation->id,
                        'parent_id' => null,
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
                    ],
                ];
            }

            foreach ($preparedItems as $itemInfo) {
                if ($itemInfo['parent_reference'] === null) {
                    continue;
                }

                if (! isset($knownReferences[$itemInfo['parent_reference']])) {
                    throw ValidationException::withMessages([
                        'items' => ["Parent reference '{$itemInfo['parent_reference']}' was not found in the submitted items for this navigation."],
                    ]);
                }
            }

            $referenceMap = [];
            foreach ($preparedItems as $itemInfo) {
                $item = NavigationItem::create($itemInfo['data']);
                $referenceMap[$itemInfo['reference']] = $item->id;
            }

            foreach ($preparedItems as $itemInfo) {
                if ($itemInfo['parent_reference'] === null) {
                    continue;
                }

                $itemId = $referenceMap[$itemInfo['reference']] ?? null;
                $parentId = $referenceMap[$itemInfo['parent_reference']] ?? null;

                if ($itemId === null || $parentId === null) {
                    throw ValidationException::withMessages([
                        'items' => ['Could not resolve parent-child mapping while replacing navigation items.'],
                    ]);
                }

                NavigationItem::whereKey($itemId)->update(['parent_id' => $parentId]);
            }
        });

        $navigation->refresh();
        $navigation->load('items');

        return [
            'success' => true,
            'message' => "Navigation '{$navigation->name}' updated with " . count($items) . ' items',
            'items_count' => $navigation->items->count(),
        ];
    }

    /**
     * @param array{id?: ?string, temp_id?: ?string} $itemData
     */
    private function resolveItemReference(array $itemData, int $order): string
    {
        if (! empty($itemData['temp_id'])) {
            return 'temp:' . $itemData['temp_id'];
        }

        if (! empty($itemData['id'])) {
            return 'id:' . $itemData['id'];
        }

        return 'index:' . $order;
    }

    /**
     * @param array{parent_id?: ?string, parent_temp_id?: ?string} $itemData
     *
     * parent_temp_id takes precedence over parent_id when both are provided.
     */
    private function resolveParentReference(array $itemData): ?string
    {
        if (! empty($itemData['parent_temp_id'])) {
            return 'temp:' . $itemData['parent_temp_id'];
        }

        if (! empty($itemData['parent_id'])) {
            return 'id:' . $itemData['parent_id'];
        }

        return null;
    }
}
