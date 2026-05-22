<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description(
    'List navigation items grouped by location (header, footer_primary, footer_contact, footer_legal). Optionally filter by location.',
)]
class ListNavigationItems extends Tool
{
    public function handle(Request $request): Response
    {
        $query = NavigationItem::query()->orderBy('location')->orderBy('sort')->orderBy('id');

        /** @var string|null $location */
        $location = $request->get('location');

        if ($location !== null && $location !== '') {
            $locationEnum = NavigationLocation::tryFrom($location);

            if ($locationEnum === null) {
                return Response::error(\sprintf(
                    'Unknown location "%s". Allowed: %s.',
                    $location,
                    implode(', ', array_map(static fn(NavigationLocation $loc): string => $loc->value, NavigationLocation::cases())),
                ));
            }

            $query->where('location', $locationEnum);
        }

        $items = $query->get();

        return Response::json(
            $items->map(static fn(NavigationItem $item): array => [
                'id' => $item->id,
                'location' => $item->location->value,
                'label' => $item->label,
                'url' => $item->url,
                'condition' => $item->condition?->value,
                'open_in_new_tab' => $item->open_in_new_tab,
                'is_cta' => $item->is_cta,
                'is_visible' => $item->is_visible,
                'umami_event_target' => $item->umami_event_target,
                'sort' => $item->sort,
            ])->all(),
        );
    }

    /**
     * @return array<string, JsonSchema>
     */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()->description('Optional location filter: header, footer_primary, footer_contact, footer_legal.'),
        ];
    }
}
