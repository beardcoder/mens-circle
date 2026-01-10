<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events\Pages;

use App\Filament\Clusters\Events\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
