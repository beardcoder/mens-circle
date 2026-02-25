<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
