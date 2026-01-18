<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants\Resources\Participants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Persönliche Daten')
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Telefonnummer')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
