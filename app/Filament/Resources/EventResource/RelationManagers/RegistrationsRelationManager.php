<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\RegistrationStatus;
use App\Filament\Forms\ParticipantForms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
                    ->schema([ParticipantForms::participantSelect(), ]),

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
            ->headerActions([CreateAction::make(), ])
            ->recordActions([EditAction::make(), ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), ]), ])
            ->defaultSort('registered_at', 'desc');
    }
}
