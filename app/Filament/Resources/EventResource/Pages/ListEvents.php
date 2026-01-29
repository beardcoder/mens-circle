<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make(), ];
    }
}
