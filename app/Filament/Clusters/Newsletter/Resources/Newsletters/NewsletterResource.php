<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\Newsletters;

use App\Filament\Clusters\Newsletter\NewsletterCluster;
use App\Filament\Clusters\Newsletter\Resources\Newsletters\Pages\ListNewsletters;
use App\Filament\Clusters\Newsletter\Resources\Newsletters\Pages\ViewNewsletter;
use App\Filament\Clusters\Newsletter\Resources\Newsletters\Schemas\NewsletterForm;
use App\Filament\Clusters\Newsletter\Resources\Newsletters\Tables\NewsletterTable;
use App\Models\Newsletter;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static ?string $cluster = NewsletterCluster::class;

    protected static ?string $navigationLabel = 'Archiv';

    protected static ?string $modelLabel = 'Newsletter';

    protected static ?string $pluralModelLabel = 'Newsletter';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return NewsletterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletters::route('/'),
            'view' => ViewNewsletter::route('/{record}'),
        ];
    }
}
