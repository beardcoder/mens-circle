<?php

declare(strict_types=1);

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Enums\NavigationLocation;
use App\Filament\Resources\NavigationItemResource;
use App\Models\NavigationItem;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->mutateDataUsing(static fn(array $data): array => [
                    ...$data,
                    'sort' => (int) NavigationItem::query()->where('location', $data['location'] ?? null)->max('sort') + 1,
                ]),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    #[Override]
    public function getTabs(): array
    {
        $tabs = [];

        foreach (NavigationLocation::cases() as $location) {
            $tabs[$location->value] = Tab::make($location->getLabel())
                ->badge(NavigationItem::query()->where('location', $location->value)->count())
                ->modifyQueryUsing(static fn(Builder $query): Builder => $query->where('location', $location->value));
        }

        return $tabs;
    }
}
