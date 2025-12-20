<?php

namespace App\Filament\Resources\NewsletterSubscriptions\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('E-Mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktiv',
                        'unsubscribed' => 'Abgemeldet',
                    ])
                    ->required()
                    ->default('active'),

                DateTimePicker::make('subscribed_at')
                    ->label('Angemeldet am')
                    ->disabled()
                    ->displayFormat('d.m.Y H:i'),

                DateTimePicker::make('unsubscribed_at')
                    ->label('Abgemeldet am')
                    ->disabled()
                    ->displayFormat('d.m.Y H:i'),
            ]);
    }
}
