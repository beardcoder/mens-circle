<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;
}
