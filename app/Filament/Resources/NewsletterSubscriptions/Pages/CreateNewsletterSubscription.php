<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscriptions\Pages;

use App\Filament\Resources\NewsletterSubscriptions\NewsletterSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSubscription extends CreateRecord
{
    protected static string $resource = NewsletterSubscriptionResource::class;
}
