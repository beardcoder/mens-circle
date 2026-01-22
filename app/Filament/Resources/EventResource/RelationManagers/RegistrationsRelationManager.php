<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\RegistrationStatus;
use App\Models\Participant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Anmeldungen';

    protected static ?string $recordTitleAttribute = 'participant.email';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                            ->default(RegistrationStatus::Registered->value)
                            ->required()
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

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('participant'))
            ->columns([
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
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Anmeldestatus')
                    ->options(RegistrationStatus::options()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('registered_at', 'desc');
    }
}
