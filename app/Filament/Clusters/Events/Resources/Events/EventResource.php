<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events;

use App\Filament\Clusters\Events\EventsCluster;
use App\Filament\Clusters\Events\Resources\Events\Pages\CreateEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\EditEvent;
use App\Filament\Clusters\Events\Resources\Events\Pages\ListEvents;
use App\Filament\Clusters\Events\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
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
        return $schema
            ->components([
                Section::make('Grundinformationen')
                    ->description('Titel, Beschreibung und Bild für das Event')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('z.B. Männerabend im Januar')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', null)),
                        TextInput::make('slug')
                            ->label('URL-Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Wird automatisch aus dem Titel generiert'),
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(4)
                            ->placeholder('Beschreibe das Event...')
                            ->helperText('Kurze Beschreibung für die Event-Übersicht'),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Event-Bild')
                            ->image()
                            ->collection('event_image')
                            ->disk('public')
                            ->responsiveImages()
                            ->imageEditor()
                            ->helperText('Empfohlen: 16:9 Format, mindestens 1200x675px'),
                    ]),

                Section::make('Datum & Zeit')
                    ->description('Wann findet das Event statt?')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('event_date')
                            ->label('Veranstaltungsdatum')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->minDate(now()),
                        TimePicker::make('start_time')
                            ->label('Startzeit')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        TimePicker::make('end_time')
                            ->label('Endzeit')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),
                    ]),

                Section::make('Ort')
                    ->description('Wo findet das Event statt?')
                    ->columns(3)
                    ->schema([
                        TextInput::make('location')
                            ->label('Ort/Veranstaltungsort')
                            ->required()
                            ->default('Straubing')
                            ->maxLength(255)
                            ->placeholder('z.B. Gemeindehaus St. Jakob')
                            ->columnSpanFull(),
                        TextInput::make('street')
                            ->label('Straße & Hausnummer')
                            ->maxLength(255)
                            ->placeholder('z.B. Hauptstraße 1')
                            ->columnSpan(2),
                        TextInput::make('postal_code')
                            ->label('PLZ')
                            ->maxLength(10)
                            ->placeholder('z.B. 94315')
                            ->columnSpan(1),
                        TextInput::make('city')
                            ->label('Stadt')
                            ->maxLength(255)
                            ->placeholder('z.B. Straubing')
                            ->default('Straubing')
                            ->columnSpanFull(),
                        Textarea::make('location_details')
                            ->label('Ortsdetails für Teilnehmer')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Zusätzliche Informationen wie Parkplatz, Eingang, etc.')
                            ->helperText('Wird nur angemeldeten Teilnehmern angezeigt'),
                    ]),

                Section::make('Teilnehmer & Kosten')
                    ->description('Teilnehmerbegrenzung und Kostenbasis')
                    ->columns(2)
                    ->schema([
                        TextInput::make('max_participants')
                            ->label('Maximale Teilnehmer')
                            ->required()
                            ->numeric()
                            ->default(8)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Anzahl der verfügbaren Plätze'),
                        TextInput::make('cost_basis')
                            ->label('Kostenbasis')
                            ->required()
                            ->default('Auf Spendenbasis')
                            ->maxLength(255)
                            ->placeholder('z.B. Kostenlos, 10€, Auf Spendenbasis'),
                    ]),

                Section::make('Veröffentlichung')
                    ->description('Sichtbarkeit auf der Website')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Event veröffentlichen')
                            ->default(false)
                            ->helperText('Aktivieren, um das Event auf der Website anzuzeigen')
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('activeRegistrations'))
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): ?string => $record->description ? str($record->description)->limit(50)->toString() : null)
                    ->wrap(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->description(fn ($record): string => $record->start_time->format('H:i').' - '.$record->end_time->format('H:i'))
                    ->color(fn ($record): string => $record->isPast ? 'gray' : ($record->event_date->isToday() ? 'warning' : 'primary')),
                TextColumn::make('location')
                    ->label('Ort')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('active_registrations_count')
                    ->label('Anmeldungen')
                    ->formatStateUsing(fn ($record): string => $record->active_registrations_count.' / '.$record->max_participants)
                    ->badge()
                    ->color(fn ($record): string => $record->isFull ? 'danger' : ($record->active_registrations_count > ($record->max_participants * 0.8) ? 'warning' : 'success'))
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_published')
                    ->label('Veröffentlichungsstatus')
                    ->options([
                        '1' => 'Veröffentlicht',
                        '0' => 'Entwurf',
                    ]),
                Filter::make('upcoming')
                    ->label('Kommende Events')
                    ->query(fn (Builder $query): Builder => $query->where('event_date', '>=', now()))
                    ->toggle()
                    ->default(),
                Filter::make('past')
                    ->label('Vergangene Events')
                    ->query(fn (Builder $query): Builder => $query->where('event_date', '<', now()))
                    ->toggle(),
                Filter::make('full')
                    ->label('Ausgebuchte Events')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(SELECT COUNT(*) FROM registrations WHERE event_id = events.id AND status IN (?, ?)) >= max_participants', ['registered', 'attended']))
                    ->toggle(),
                TrashedFilter::make()
                    ->label('Gelöschte Events'),
            ])
            ->deferFilters(false)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_date', 'desc')
            ->persistFiltersInSession()
            ->striped();
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
