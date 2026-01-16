<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Schemas;

use App\Enums\NewsletterSubscriptionStatus;
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
                Section::make('Abonnent-Informationen')
                    ->description('E-Mail-Adresse des Abonnenten')
                    ->schema([
                        TextInput::make('email')
                            ->label('E-Mail-Adresse')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('beispiel@email.de'),
                    ]),

                Section::make('Abonnement-Status')
                    ->description('Status und Zeitstempel')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(NewsletterSubscriptionStatus::options())
                            ->required()
                            ->default(NewsletterSubscriptionStatus::Active->value)
                            ->native(false)
                            ->columnSpanFull(),
                        DateTimePicker::make('subscribed_at')
                            ->label('Angemeldet am')
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
