<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\EventRegistrations\Schemas;

use App\Enums\RegistrationStatus;
use App\Models\Participant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
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
}
