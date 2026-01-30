<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Models\Participant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ParticipantForms
{
    public static function participantSelect(): Select
    {
        return Select::make('participant_id')
            ->label('Teilnehmer')
            ->relationship('participant', 'email')
            ->getOptionLabelFromRecordUsing(
                fn (Participant $record): string => "{$record->fullName} ({$record->email})",
            )
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
            ->native(false);
    }
}
