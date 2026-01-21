<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Participant;
use App\Models\Registration;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Anmeldungen';

    protected static ?string $modelLabel = 'Anmeldung';

    protected static ?string $pluralModelLabel = 'Anmeldungen';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Veranstaltungsauswahl')
                    ->description('Wähle das Event für die Anmeldung')
                    ->schema([
                        Select::make('event_id')
                            ->label('Veranstaltung')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),

                Section::make('Teilnehmer')
                    ->description('Wähle einen bestehenden Teilnehmer oder erstelle einen neuen')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Teilnehmer')
                            ->relationship('participant', 'email')
                            ->getOptionLabelFromRecordUsing(fn (Participant $record): string => "{$record->fullName} ({$record->email})")
                            ->required()
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('first_name')
                                    ->label('Vorname')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Nachname')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-Mail-Adresse')
                                    ->email()
                                    ->required()
                                    ->unique()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Telefonnummer')
                                    ->tel()
                                    ->maxLength(30),
                            ])
                            ->native(false),
                    ]),

                Section::make('Anmeldestatus')
                    ->description('Status und Zeitpunkte der Anmeldung')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(RegistrationStatus::options())
                            ->required()
                            ->default(RegistrationStatus::Registered->value)
                            ->native(false),
                        DateTimePicker::make('registered_at')
                            ->label('Angemeldet am')
                            ->native(false)
                            ->default(now())
                            ->displayFormat('d.m.Y H:i'),
                        DateTimePicker::make('cancelled_at')
                            ->label('Abgesagt am')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.event_date')
                    ->label('Event-Datum')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Veranstaltung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant.first_name')
                    ->label('Vorname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant.last_name')
                    ->label('Nachname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant.email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('participant.phone')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (RegistrationStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (RegistrationStatus $state): string => $state->getLabel())
                    ->sortable(),
                TextColumn::make('registered_at')
                    ->label('Angemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->label('Abgesagt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Gelöschte Anmeldungen'),
                SelectFilter::make('status')
                    ->label('Anmeldestatus')
                    ->options(RegistrationStatus::options()),
                SelectFilter::make('event')
                    ->label('Veranstaltung')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event.event_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
