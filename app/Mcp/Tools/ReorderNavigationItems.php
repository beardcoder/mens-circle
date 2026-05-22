<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description('Reorder navigation items within one location. Pass the full ordered list of item ids that belong to that location; the sort field is rewritten as 10, 20, 30, ... to leave room for inserts. Items belonging to the location but missing from the list are pushed to the end.')]
class ReorderNavigationItems extends Tool
{
    public function handle(Request $request): Response
    {
        /** @var string $location */
        $location = $request->get('location');

        $locationEnum = NavigationLocation::tryFrom($location);

        if ($locationEnum === null) {
            return Response::error("Unknown location \"{$location}\".");
        }

        /** @var array<int, int|string> $ids */
        $ids = $request->get('ids');

        $items = NavigationItem::query()->where('location', $locationEnum)->get()->keyBy('id');

        $sort = 10;
        $seen = [];

        DB::transaction(static function () use ($ids, $items, &$sort, &$seen): void {
            foreach ($ids as $id) {
                $intId = (int) $id;

                if (!$items->has($intId)) {
                    continue;
                }

                /** @var NavigationItem $item */
                $item = $items->get($intId);
                $item->sort = $sort;
                $item->save();
                $seen[$intId] = true;
                $sort += 10;
            }

            // Push any items missing from the list to the end.
            foreach ($items as $item) {
                if (isset($seen[$item->id])) {
                    continue;
                }

                $item->sort = $sort;
                $item->save();
                $sort += 10;
            }
        });

        return Response::text(\sprintf(
            'Reordered %d navigation items in location "%s".',
            \count($seen),
            $locationEnum->value,
        ));
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
                ->description('Navigation area to reorder: header, footer_primary, footer_contact, footer_legal.')
                ->required(),
            'ids' => $schema
                ->array()
                ->description('Ordered list of navigation item ids belonging to that location.')
                ->items($schema->integer())
                ->required(),
        ];
    }
}
