<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Testimonials\Pages;

use App\Filament\Clusters\Content\Resources\Testimonials\TestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
