<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterResource\Pages;

use App\Filament\Resources\NewsletterResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewNewsletter extends ViewRecord
{
    protected static string $resource = NewsletterResource::class;
}
