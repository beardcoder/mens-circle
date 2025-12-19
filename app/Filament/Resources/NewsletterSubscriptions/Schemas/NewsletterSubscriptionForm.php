<?php

namespace App\Filament\Resources\NewsletterSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktiv',
                        'unsubscribed' => 'Abgemeldet',
                    ])
                    ->required()
                    ->default('active'),
                TextInput::make('token')
                    ->label('Token')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Wird automatisch generiert'),
                DateTimePicker::make('subscribed_at')
                    ->label('Angemeldet am')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('unsubscribed_at')
                    ->label('Abgemeldet am')
                    ->native(false),
            ]);
    }
}
