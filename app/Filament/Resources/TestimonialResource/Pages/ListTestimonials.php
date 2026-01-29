<?php

declare(strict_types=1);

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Resources\TestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make(), ];
    }
}
