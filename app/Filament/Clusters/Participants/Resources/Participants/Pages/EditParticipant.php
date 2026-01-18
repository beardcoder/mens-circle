<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants\Resources\Participants\Pages;

use App\Filament\Clusters\Participants\Resources\Participants\ParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParticipant extends EditRecord
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
