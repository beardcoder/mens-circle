<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
     * @var array<int, string>
     */
    private const ALLOWED_TARGETS = ['_self', '_blank', '_parent', '_top'];

    /**
     * @param array{navigation_id: string, label: string, url?: ?string, route_name?: ?string, route_params?: ?array, anchor?: ?string, target?: ?string, parent_id?: ?string, is_active?: ?bool, icon?: ?string, css_class?: ?string, data_attributes?: ?array} $input
     */
    public function __invoke(array $input): array
    {
        $validated = Validator::make($input, [
            'navigation_id' => ['required', 'uuid', 'exists:navigations,id'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'route_params' => ['nullable', 'array'],
            'anchor' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', Rule::in(self::ALLOWED_TARGETS)],
            'parent_id' => ['nullable', 'uuid'],
            'is_active' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'data_attributes' => ['nullable', 'array'],
        ])->validate();

        $navigation = Navigation::findOrFail($validated['navigation_id']);

        if (! empty($validated['parent_id'])) {
            $parentExists = NavigationItem::query()
                ->whereKey($validated['parent_id'])
                ->where('navigation_id', $navigation->id)
                ->exists();

            if (! $parentExists) {
                throw ValidationException::withMessages([
                    'parent_id' => ['The parent_id must reference an existing navigation item in the same navigation.'],
                ]);
            }
        }

        $maxOrder = $navigation->items()->max('order') ?? -1;

        $item = NavigationItem::create([
            'navigation_id' => $navigation->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'label' => $validated['label'],
            'url' => $validated['url'] ?? null,
            'route_name' => $validated['route_name'] ?? null,
            'route_params' => $validated['route_params'] ?? null,
            'anchor' => $validated['anchor'] ?? null,
            'target' => $validated['target'] ?? '_self',
            'order' => $maxOrder + 1,
            'is_active' => $validated['is_active'] ?? true,
            'icon' => $validated['icon'] ?? null,
            'css_class' => $validated['css_class'] ?? null,
            'data_attributes' => $validated['data_attributes'] ?? null,
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
