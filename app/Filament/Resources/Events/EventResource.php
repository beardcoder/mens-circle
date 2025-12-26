<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titel')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Beschreibung')
                    ->rows(4)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Event-Bild')
                    ->image()
                    ->directory('events')
                    ->visibility('public')
                    ->imageEditor()
                    ->columnSpanFull()
                    ->helperText('Optionales Bild für die Event-Detailseite (empfohlen: 16:9 Format)'),
                DateTimePicker::make('event_date')
                    ->label('Veranstaltungsdatum')
                    ->required()
                    ->native(false),
                TimePicker::make('start_time')
                    ->label('Startzeit')
                    ->required()
                    ->seconds(false),
                TimePicker::make('end_time')
                    ->label('Endzeit')
                    ->required()
                    ->seconds(false),
                TextInput::make('location')
                    ->label('Ort')
                    ->required()
                    ->default('Straubing')
                    ->maxLength(255),
                TextInput::make('street')
                    ->label('Straße & Hausnummer')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('postal_code')
                    ->label('PLZ')
                    ->maxLength(10),
                TextInput::make('city')
                    ->label('Stadt')
                    ->maxLength(255),
                Textarea::make('location_details')
                    ->label('Ortsdetails (nach Anmeldung)')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Diese Details werden nur registrierten Teilnehmern angezeigt.'),
                TextInput::make('max_participants')
                    ->label('Max. Teilnehmer')
                    ->required()
                    ->numeric()
                    ->default(8)
                    ->minValue(1),
                TextInput::make('cost_basis')
                    ->label('Kostenbasis')
                    ->required()
                    ->default('Auf Spendenbasis')
                    ->maxLength(255),
                Toggle::make('is_published')
                    ->label('Veröffentlicht')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Von')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Bis')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Ort')
                    ->searchable(),
                TextColumn::make('confirmedRegistrations')
                    ->label('Anmeldungen')
                    ->counts('confirmedRegistrations')
                    ->formatStateUsing(fn ($record): string => $record->confirmedRegistrations()->count().' / '.$record->max_participants)
                    ->badge()
                    ->color(fn ($record): string => $record->isFull() ? 'danger' : 'success'),
                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
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
