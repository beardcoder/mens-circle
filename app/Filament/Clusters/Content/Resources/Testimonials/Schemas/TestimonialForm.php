<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Testimonials\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('quote')
                    ->label('Zitat')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Das Testimonial-Zitat des Teilnehmers.'),

                TextInput::make('author_name')
                    ->label('Name')
                    ->maxLength(255)
                    ->helperText('Optional: Name des Teilnehmers. Leer lassen für anonyme Zitate.'),

                TextInput::make('email')
                    ->label('E-Mail')
                    ->email()
                    ->maxLength(255)
                    ->helperText('Wird nicht veröffentlicht, nur für Rückfragen.'),

                TextInput::make('role')
                    ->label('Rolle/Beschreibung')
                    ->maxLength(255)
                    ->helperText('z.B. "Teilnehmer seit 2023" oder "Gründungsmitglied".'),

                TextInput::make('sort_order')
                    ->label('Sortierung')
                    ->numeric()
                    ->default(0)
                    ->helperText('Niedrigere Zahlen erscheinen zuerst.'),

                Toggle::make('is_published')
                    ->label('Veröffentlicht')
                    ->default(false)
                    ->helperText('Testimonial auf der Website anzeigen.')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state) {
                            $set('published_at', now());
                        }
                    }),

                DateTimePicker::make('published_at')
                    ->label('Veröffentlichungsdatum')
                    ->helperText('Wird automatisch gesetzt, wenn das Testimonial veröffentlicht wird.'),
            ]);
    }
}
