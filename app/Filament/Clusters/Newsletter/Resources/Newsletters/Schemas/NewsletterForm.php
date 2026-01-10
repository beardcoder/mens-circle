<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\Newsletters\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
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
                RichEditor::make('content')
                    ->label('Inhalt')
                    ->disabled()
                    ->dehydrated(false)
                    ->toolbarButtons([]),
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
