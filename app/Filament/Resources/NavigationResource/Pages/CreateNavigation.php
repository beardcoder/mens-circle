<?php

declare(strict_types=1);

namespace App\Filament\Resources\NavigationResource\Pages;

use App\Filament\Resources\NavigationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNavigation extends CreateRecord
{
    protected static string $resource = NavigationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
