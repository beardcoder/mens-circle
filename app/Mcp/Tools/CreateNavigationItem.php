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
    'Create a new navigation item in the given location. URL accepts absolute URLs or internal paths ("/atemuebung"). Use the separate "anchor" field to link to a content block anchor (e.g. anchor="ueber" appends "#ueber" to the resolved URL). Use condition="next_event" together with an empty url to render a dynamic link to the next upcoming event.',
)]
class CreateNavigationItem extends Tool
{
    public function handle(Request $request): Response
    {
        $locationValues = implode(',', array_column(NavigationLocation::cases(), 'value'));
        $conditionValues = implode(',', array_column(NavigationCondition::cases(), 'value'));

        $validator = Validator::make($request->toArray(), [
            'location' => ['required', 'string', 'in:' . $locationValues],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'anchor' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', 'string', 'in:' . $conditionValues],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_cta' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'umami_event_target' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return Response::error('Invalid input: ' . $validator->errors()->toJson());
        }

        /** @var array{location: string, label: string, url?: ?string, anchor?: ?string, condition?: ?string, open_in_new_tab?: ?bool, is_cta?: ?bool, is_visible?: ?bool, umami_event_target?: ?string, sort?: ?int} $data */
        $data = $validator->validated();

        $condition = ($data['condition'] ?? '') !== '' ? NavigationCondition::from((string) $data['condition']) : null;

        $item = NavigationItem::query()->create([
            'location' => NavigationLocation::from($data['location']),
            'label' => $data['label'],
            'url' => $data['url'] ?? '',
            'anchor' => self::normaliseAnchor($data['anchor'] ?? null),
            'condition' => $condition,
            'open_in_new_tab' => $data['open_in_new_tab'] ?? false,
            'is_cta' => $data['is_cta'] ?? false,
            'is_visible' => $data['is_visible'] ?? true,
            'umami_event_target' => $data['umami_event_target'] ?? null,
            'sort' => $data['sort'] ?? 0,
        ]);

        return Response::json([
            'id' => $item->id,
            'location' => $item->location->value,
            'label' => $item->label,
            'url' => $item->url,
            'anchor' => $item->anchor,
            'condition' => $item->condition?->value,
            'sort' => $item->sort,
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        /** @var array<string, JsonSchema> $properties */
        return [
            'location' => $schema
                ->string()
                ->description('Navigation area: header, footer_primary, footer_contact, footer_legal.')
                ->required(),
            'label' => $schema->string()->description('Visible link text (max 255 chars).')->required(),
            'url' => $schema
                ->string()
                ->description('Target URL/path (max 2048 chars). Leave empty for the home page or when using condition="next_event".'),
            'anchor' => $schema
                ->string()
                ->description(
                    'Optional anchor name without leading "#" (max 255 chars). Appended to the resolved URL as fragment, e.g. anchor="ueber" => "/#ueber" or "/atemuebung#ueber". Must match the anchor set on a content block to scroll to it.',
                ),
            'condition' => $schema
                ->string()
                ->description('Optional dynamic condition. Allowed: next_event. Empty string clears the condition.'),
            'open_in_new_tab' => $schema->boolean()->description('Open link in new tab. Defaults to false.'),
            'is_cta' => $schema->boolean()->description('Render as primary call-to-action button (header only). Defaults to false.'),
            'is_visible' => $schema->boolean()->description('Whether the item is rendered. Defaults to true.'),
            'umami_event_target' => $schema
                ->string()
                ->description('Optional value for data-umami-event-target (analytics, max 255 chars).'),
            'sort' => $schema
                ->integer()
                ->description('Sort order within the location (lower numbers come first, must be >= 0). Defaults to 0.'),
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
