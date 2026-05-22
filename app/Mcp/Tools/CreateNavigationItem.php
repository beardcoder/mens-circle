<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description('Create a new navigation item in the given location. URL accepts absolute URLs, internal paths ("/atemuebung") or anchors that expand to the home page (e.g. "#ueber"). Use condition="next_event" together with an empty url to render a dynamic link to the next upcoming event.')]
class CreateNavigationItem extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $location */
        $location = $request->get('location');

        $locationEnum = NavigationLocation::tryFrom($location);

        if ($locationEnum === null) {
            return Response::error(\sprintf(
                'Unknown location "%s". Allowed: %s.',
                $location,
                implode(', ', array_map(static fn(NavigationLocation $loc): string => $loc->value, NavigationLocation::cases())),
            ));
        }

        /** @var string|null $condition */
        $condition = $request->get('condition');
        $conditionEnum = null;

        if ($condition !== null && $condition !== '') {
            $conditionEnum = NavigationCondition::tryFrom($condition);

            if ($conditionEnum === null) {
                return Response::error(\sprintf(
                    'Unknown condition "%s". Allowed: %s.',
                    $condition,
                    implode(', ', array_map(static fn(NavigationCondition $c): string => $c->value, NavigationCondition::cases())),
                ));
            }
        }

        $item = NavigationItem::create([
            'location' => $locationEnum,
            'label' => $request->get('label'),
            'url' => $request->get('url') ?? '',
            'condition' => $conditionEnum,
            'open_in_new_tab' => (bool) ($request->get('open_in_new_tab') ?? false),
            'is_cta' => (bool) ($request->get('is_cta') ?? false),
            'is_visible' => (bool) ($request->get('is_visible') ?? true),
            'umami_event_target' => $request->get('umami_event_target'),
            'sort' => (int) ($request->get('sort') ?? 0),
        ]);

        return Response::json([
            'id' => $item->id,
            'location' => $item->location->value,
            'label' => $item->label,
            'url' => $item->url,
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
        return [
            'location' => $schema
                ->string()
                ->description('Navigation area: header, footer_primary, footer_contact, footer_legal.')
                ->required(),
            'label' => $schema->string()->description('Visible link text.')->required(),
            'url' => $schema->string()->description('Target URL/path/anchor. Leave empty when using condition="next_event".'),
            'condition' => $schema->string()->description('Optional dynamic condition. Allowed: next_event.'),
            'open_in_new_tab' => $schema->boolean()->description('Open link in new tab. Defaults to false.'),
            'is_cta' => $schema->boolean()->description('Render as primary call-to-action button (header only). Defaults to false.'),
            'is_visible' => $schema->boolean()->description('Whether the item is rendered. Defaults to true.'),
            'umami_event_target' => $schema->string()->description('Optional value for data-umami-event-target (analytics).'),
            'sort' => $schema->integer()->description('Sort order within the location (lower numbers come first). Defaults to 0.'),
        ];
    }
}
