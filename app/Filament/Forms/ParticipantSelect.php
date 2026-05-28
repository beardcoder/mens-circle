<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Models\Participant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Searchable participant picker with an inline "create new" form. Shared
 * between RegistrationResource and NewsletterSubscriptionResource so the
 * field behaves identically wherever an admin links a record to a person.
 */
final class ParticipantSelect
{
    public static function make(string $name = 'participant_id', string $label = 'Teilnehmer'): Select
    {
        return Select::make($name)
            ->label($label)
            ->relationship('participant', 'email')
            ->getOptionLabelFromRecordUsing(static fn(Participant $record): string => $record->fullName !== ''
                ? "{$record->fullName} ({$record->email})"
                : $record->email)
            ->required()
            ->searchable(['first_name', 'last_name', 'email'])
            ->preload()
            ->native(false)
            ->createOptionForm([
                TextInput::make('first_name')->label('Vorname')->maxLength(255),
                TextInput::make('last_name')->label('Nachname')->maxLength(255),
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->email()
                    ->required()
                    ->unique()
                    ->maxLength(255),
                TextInput::make('phone')->label('Telefonnummer')->tel()->maxLength(30),
            ]);
    }
}
