<?php

declare(strict_types=1);

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Filament\Resources\NavigationItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditNavigationItem extends EditRecord
{
    protected static string $resource = NavigationItemResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
