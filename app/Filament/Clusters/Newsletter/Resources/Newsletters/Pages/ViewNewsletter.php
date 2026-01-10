<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\Newsletters\Pages;

use App\Filament\Clusters\Newsletter\Resources\Newsletters\NewsletterResource;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsletter extends ViewRecord
{
    protected static string $resource = NewsletterResource::class;
}
