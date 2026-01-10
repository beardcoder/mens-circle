<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events;

use App\Filament\Clusters\Events\EventsCluster;
use App\Filament\Clusters\Events\Resources\Events\Pages\CreateEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\EditEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\ListEvents;
use App\Filament\Clusters\Events\Resources\Events\Schemas\EventForm;
use App\Filament\Clusters\Events\Resources\Events\Tables\EventTable;
use App\Models\Event;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $cluster = EventsCluster::class;

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
