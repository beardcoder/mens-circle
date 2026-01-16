<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\EventRegistrations\Schemas;

use App\Enums\EventRegistrationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                Section::make('Teilnehmer-Informationen')
                    ->description('Persönliche Daten des Teilnehmers')
                    ->columns(2)
                    ->schema([
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
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('phone_number')
                            ->label('Handynummer')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpanFull(),
                        Toggle::make('privacy_accepted')
                            ->label('Datenschutzerklärung akzeptiert')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Anmeldestatus')
                    ->description('Status und Bestätigung der Anmeldung')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(EventRegistrationStatus::options())
                            ->required()
                            ->default(EventRegistrationStatus::Confirmed->value)
                            ->native(false),
                        DateTimePicker::make('confirmed_at')
                            ->label('Bestätigt am')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                    ]),
            ]);
    }
}
