<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Veranstaltung')
                    ->relationship('event', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('first_name')
                    ->label('Vorname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('Nachname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Toggle::make('privacy_accepted')
                    ->label('Datenschutz akzeptiert')
                    ->default(false),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'confirmed' => 'Bestätigt',
                        'cancelled' => 'Abgesagt',
                        'waitlist' => 'Warteliste',
                    ])
                    ->required()
                    ->default('confirmed'),
                DateTimePicker::make('confirmed_at')
                    ->label('Bestätigt am')
                    ->native(false),
            ]);
    }
}
