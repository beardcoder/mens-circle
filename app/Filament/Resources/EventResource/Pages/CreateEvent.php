<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
}
