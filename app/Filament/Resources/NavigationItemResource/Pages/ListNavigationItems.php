<?php

declare(strict_types=1);

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Filament\Resources\NavigationItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
