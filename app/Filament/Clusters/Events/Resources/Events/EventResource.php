<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events;

use App\Filament\Clusters\Events\EventsCluster;
use App\Filament\Clusters\Events\Resources\Events\Pages\CreateEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\EditEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\ListEvents;
use App\Filament\Clusters\Events\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Clusters\Events\Resources\Events\Schemas\EventForm;
use App\Filament\Clusters\Events\Resources\Events\Tables\EventTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $cluster = EventsCluster::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Events';

    protected static ?string $modelLabel = 'Event';

    protected static ?string $pluralModelLabel = 'Events';

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
        return [
            RegistrationsRelationManager::class,
        ];
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
