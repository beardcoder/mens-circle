<?php

declare(strict_types=1);

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParticipants extends ListRecords
{
    protected static string $resource = ParticipantResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make(), ];
    }
}
