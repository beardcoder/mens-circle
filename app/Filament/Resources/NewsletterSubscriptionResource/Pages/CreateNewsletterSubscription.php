<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscriptionResource\Pages;

use App\Filament\Resources\NewsletterSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateNewsletterSubscription extends CreateRecord
{
    protected static string $resource = NewsletterSubscriptionResource::class;
}
