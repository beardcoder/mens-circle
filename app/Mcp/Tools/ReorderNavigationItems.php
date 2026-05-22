<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Override;

#[Description(
    'Reorder navigation items within one location. Pass the full ordered list of item ids that belong to that location; the sort field is rewritten as 10, 20, 30, ... to leave room for inserts. All ids must exist and belong to the selected location, and duplicates are rejected. Items belonging to the location but missing from the list are pushed to the end with preserved relative order.',
)]
class ReorderNavigationItems extends Tool
{
    public function handle(Request $request): Response
    {
        $locationValues = array_column(NavigationLocation::cases(), 'value');

        $validator = Validator::make($request->toArray(), [
            'location' => ['required', 'string', 'in:' . implode(',', $locationValues)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return Response::error('Invalid input: ' . $validator->errors()->toJson());
        }

        /** @var array{location: string, ids: array<int, int>} $data */
        $data = $validator->validated();

        $locationEnum = NavigationLocation::from($data['location']);

        /** @var array<int, int> $ids */
        $ids = array_map(static fn(int|string $id): int => (int) $id, $data['ids']);

        if (\count($ids) !== \count(array_unique($ids))) {
            return Response::error('Duplicate ids are not allowed.');
        }

        $items = NavigationItem::query()->where('location', $locationEnum)->get()->keyBy('id');
        $existingIds = $items->keys()->all();

        $unknown = array_values(array_diff($ids, $existingIds));

        if ($unknown !== []) {
            return Response::error(\sprintf(
                'These ids do not exist or do not belong to location "%s": %s.',
                $locationEnum->value,
                implode(', ', $unknown),
            ));
        }

        $sort = 10;
        $reordered = 0;

        DB::transaction(static function () use ($ids, $items, &$sort, &$reordered): void {
            foreach ($ids as $id) {
                /** @var NavigationItem $item */
                $item = $items->get($id);
                $item->sort = $sort;
                $item->save();
                $sort += 10;
                ++$reordered;
            }

            // Append items belonging to the location but missing from the
            // request, preserving their existing relative order.
            $remaining = $items
                ->reject(static fn(NavigationItem $item): bool => \in_array($item->id, $ids, true))
                ->sortBy(['sort', 'id'])
                ->values();

            foreach ($remaining as $item) {
                $item->sort = $sort;
                $item->save();
                $sort += 10;
            }
        });

        return Response::text(\sprintf('Reordered %d navigation items in location "%s".', $reordered, $locationEnum->value));
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
                ->description(
                    'Ordered list of navigation item ids belonging to that location. Must be unique and all ids must exist in the location.',
                )
                ->items($schema->integer())
                ->required(),
        ];
    }
}
