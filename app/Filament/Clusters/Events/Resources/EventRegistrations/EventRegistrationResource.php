<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\EventRegistrations;

use App\Filament\Clusters\Events\EventsCluster;
use App\Filament\Clusters\Events\Resources\EventRegistrations\Pages\CreateEventRegistration;
use App\Filament\Clusters\Events\Resources\EventRegistrations\Pages\EditEventRegistration;
use App\Filament\Clusters\Events\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\Clusters\Events\Resources\EventRegistrations\Schemas\EventRegistrationForm;
use App\Filament\Clusters\Events\Resources\EventRegistrations\Tables\EventRegistrationTable;
use App\Models\Registration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $cluster = EventsCluster::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Anmeldungen';

    protected static ?string $modelLabel = 'Anmeldung';

    protected static ?string $pluralModelLabel = 'Anmeldungen';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return EventRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventRegistrationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventRegistrations::route('/'),
            'create' => CreateEventRegistration::route('/create'),
            'edit' => EditEventRegistration::route('/{record}/edit'),
        ];
    }
}
