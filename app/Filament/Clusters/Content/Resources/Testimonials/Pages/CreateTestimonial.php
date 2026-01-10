<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Testimonials\Pages;

use App\Filament\Clusters\Content\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    protected static string $resource = TestimonialResource::class;
}
