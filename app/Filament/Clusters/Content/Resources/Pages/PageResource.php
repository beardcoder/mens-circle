<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Pages;

use App\Filament\Clusters\Content\ContentCluster;
use App\Filament\Clusters\Content\Resources\Pages\Pages\CreatePage;
use App\Filament\Clusters\Content\Resources\Pages\Pages\EditPage;
use App\Filament\Clusters\Content\Resources\Pages\Pages\ListPages;
use App\Filament\Clusters\Content\Resources\Pages\Schemas\PageForm;
use App\Filament\Clusters\Content\Resources\Pages\Tables\PageTable;
use App\Models\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $cluster = ContentCluster::class;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Page>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
