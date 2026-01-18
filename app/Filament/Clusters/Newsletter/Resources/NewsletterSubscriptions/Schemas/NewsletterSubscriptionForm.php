<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Schemas;

use App\Models\Participant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teilnehmer')
                    ->description('Wähle einen bestehenden Teilnehmer oder erstelle einen neuen')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Teilnehmer')
                            ->relationship('participant', 'email')
                            ->getOptionLabelFromRecordUsing(fn (Participant $record): string => $record->fullName
                                ? "{$record->fullName} ({$record->email})"
                                : $record->email)
                            ->required()
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('first_name')
                                    ->label('Vorname')
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Nachname')
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

                Section::make('Abonnement-Status')
                    ->description('Zeitstempel des Abonnements')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('subscribed_at')
                            ->label('Angemeldet am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                        DateTimePicker::make('confirmed_at')
                            ->label('Bestätigt am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                        DateTimePicker::make('unsubscribed_at')
                            ->label('Abgemeldet am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                    ]),
            ]);
    }
}
