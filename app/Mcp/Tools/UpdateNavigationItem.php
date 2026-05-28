<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description(
    'Update a navigation item by id. Only provided fields are changed. Pass condition="" (empty string) to clear the condition. Pass anchor="" (empty string) to clear the anchor.',
)]
final class UpdateNavigationItem extends Tool
{
    public function handle(Request $request): Response
    {
        $locationValues = array_column(NavigationLocation::cases(), 'value');
        $conditionValues = array_column(NavigationCondition::cases(), 'value');

        $validator = Validator::make($request->toArray(), [
            'id' => ['required', 'integer', 'min:1'],
            'location' => ['sometimes', 'string', 'in:' . implode(',', $locationValues)],
            'label' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'anchor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'condition' => ['sometimes', 'nullable', 'string', 'in:,' . implode(',', $conditionValues)],
            'open_in_new_tab' => ['sometimes', 'boolean'],
            'is_cta' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'umami_event_target' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return Response::error('Invalid input: ' . $validator->errors()->toJson());
        }

        $data = $validator->validated();

        $item = NavigationItem::query()->find($data['id']);

        if (!$item instanceof NavigationItem) {
            return Response::error("Navigation item with id \"{$data['id']}\" not found.");
        }

        if (array_key_exists('location', $data)) {
            $item->location = NavigationLocation::from($data['location']);
        }

        if (array_key_exists('label', $data)) {
            $item->label = $data['label'];
        }

        if (array_key_exists('url', $data)) {
            $item->url = (string) ($data['url'] ?? '');
        }

        if (array_key_exists('anchor', $data)) {
            $item->anchor = self::normaliseAnchor($data['anchor']);
        }

        if (array_key_exists('condition', $data)) {
            $item->condition =
                $data['condition'] === null || $data['condition'] === '' ? null : NavigationCondition::from($data['condition']);
        }

        foreach (['open_in_new_tab', 'is_cta', 'is_visible'] as $boolField) {
            if (!array_key_exists($boolField, $data)) {
                continue;
            }

            $item->{$boolField} = (bool) $data[$boolField];
        }

        if (array_key_exists('umami_event_target', $data)) {
            $item->umami_event_target = $data['umami_event_target'];
        }

        if (array_key_exists('sort', $data)) {
            $item->sort = (int) $data['sort'];
        }

        $item->save();

        return Response::text("Navigation item #{$item->id} updated.");
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('ID of the navigation item to update.')->required(),
            'location' => $schema->string()->description('Navigation area: header, footer_primary, footer_contact, footer_legal.'),
            'label' => $schema->string()->description('Visible link text (max 255 chars).'),
            'url' => $schema->string()->description('Target URL/path (max 2048 chars). Empty for home page.'),
            'anchor' => $schema
                ->string()
                ->description(
                    'Anchor name without leading "#" (max 255 chars). Appended to the URL as fragment. Empty string clears the anchor.',
                ),
            'condition' => $schema->string()->description('Dynamic condition (allowed: next_event) or empty string to clear.'),
            'open_in_new_tab' => $schema->boolean()->description('Open link in new tab.'),
            'is_cta' => $schema->boolean()->description('Render as primary CTA button.'),
            'is_visible' => $schema->boolean()->description('Whether the item is rendered.'),
            'umami_event_target' => $schema->string()->description('Value for data-umami-event-target (max 255 chars).'),
            'sort' => $schema->integer()->description('Sort order within the location (must be >= 0).'),
        ];
    }

    /**
     * Strip whitespace and any leading "#" from an anchor value.
     */
    private static function normaliseAnchor(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalised = ltrim(trim($value), '#');

        return $normalised === '' ? null : $normalised;
    }
}
