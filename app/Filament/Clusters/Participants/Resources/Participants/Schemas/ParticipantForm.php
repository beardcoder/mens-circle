<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants\Resources\Participants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Nachname')
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
                Section::make('Newsletter')
                    ->schema([
                        Toggle::make('is_subscribed_to_newsletter')
                            ->label('Newsletter-Abonnement')
                            ->helperText('Aktivieren, um den Teilnehmer für den Newsletter anzumelden')
                            ->formatStateUsing(fn ($record) => $record?->isSubscribedToNewsletter() ?? false)
                            ->live()
                            ->afterStateUpdated(function ($state, $record): void {
                                if (! $record) {
                                    return;
                                }

                                $subscription = $record->newsletterSubscription;

                                if ($state) {
                                    // Subscribe to newsletter
                                    if ($subscription && ! $subscription->isActive()) {
                                        $subscription->resubscribe();
                                    } elseif (! $subscription) {
                                        $record->newsletterSubscription()->create([]);
                                    }
                                } elseif ($subscription?->isActive()) {
                                    // Unsubscribe from newsletter
                                    $subscription->unsubscribe();
                                }
                            })
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
