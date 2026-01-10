<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\EventRegistrations\Pages;

use App\Filament\Clusters\Events\Resources\EventRegistrations\EventRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventRegistrations extends ListRecords
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
