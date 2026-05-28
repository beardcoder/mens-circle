<?php

declare(strict_types=1);

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateParticipant extends CreateRecord
{
    protected static string $resource = ParticipantResource::class;
}
