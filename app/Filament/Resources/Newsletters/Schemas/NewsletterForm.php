<?php

namespace App\Filament\Resources\Newsletters\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->label('Betreff')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('content')
                    ->label('Inhalt')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(10),

                TextInput::make('status')
                    ->label('Status')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('recipient_count')
                    ->label('Anzahl Empfänger')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric(),

                DateTimePicker::make('sent_at')
                    ->label('Versendet am')
                    ->disabled()
                    ->dehydrated(false)
                    ->displayFormat('d.m.Y H:i'),

                DateTimePicker::make('created_at')
                    ->label('Erstellt am')
                    ->disabled()
                    ->dehydrated(false)
                    ->displayFormat('d.m.Y H:i'),
            ]);
    }
}
