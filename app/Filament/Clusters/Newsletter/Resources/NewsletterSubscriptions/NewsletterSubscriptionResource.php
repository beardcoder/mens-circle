<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions;

use App\Filament\Clusters\Newsletter\NewsletterCluster;
use App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Pages\CreateNewsletterSubscription;
use App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Pages\EditNewsletterSubscription;
use App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Pages\ListNewsletterSubscriptions;
use App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Schemas\NewsletterSubscriptionForm;
use App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Tables\NewsletterSubscriptionTable;
use App\Models\NewsletterSubscription;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static ?string $cluster = NewsletterCluster::class;

    protected static ?string $navigationLabel = 'Abonnenten';

    protected static ?string $modelLabel = 'Abonnent';

    protected static ?string $pluralModelLabel = 'Abonnenten';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return NewsletterSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterSubscriptionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscriptions::route('/'),
            'create' => CreateNewsletterSubscription::route('/create'),
            'edit' => EditNewsletterSubscription::route('/{record}/edit'),
        ];
    }
}
