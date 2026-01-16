<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Testimonials\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Testimonial-Inhalt')
                    ->description('Das Zitat des Teilnehmers')
                    ->schema([
                        Textarea::make('quote')
                            ->label('Zitat')
                            ->required()
                            ->rows(6)
                            ->maxLength(1000)
                            ->placeholder('Schreibe hier das Testimonial...')
                            ->helperText('Das Testimonial-Zitat des Teilnehmers (max. 1000 Zeichen).'),
                    ]),

                Section::make('Autor-Informationen')
                    ->description('Informationen über den Autor (optional)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Name')
                            ->maxLength(255)
                            ->placeholder('z.B. Max Mustermann')
                            ->helperText('Optional: Name des Teilnehmers. Leer lassen für anonyme Zitate.'),
                        TextInput::make('role')
                            ->label('Rolle/Beschreibung')
                            ->maxLength(255)
                            ->placeholder('z.B. Teilnehmer seit 2023')
                            ->helperText('Zusätzliche Beschreibung zum Autor.'),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('max@beispiel.de')
                            ->helperText('Wird nicht veröffentlicht, nur für interne Rückfragen.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Anzeigeoptionen')
                    ->description('Sortierung und Veröffentlichung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sortierung')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Niedrigere Zahlen erscheinen zuerst.'),
                        Toggle::make('is_published')
                            ->label('Veröffentlicht')
                            ->default(false)
                            ->helperText('Testimonial auf der Website anzeigen.')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state) {
                                    $set('published_at', now());
                                }
                            }),
                        DateTimePicker::make('published_at')
                            ->label('Veröffentlichungsdatum')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Wird automatisch gesetzt, wenn das Testimonial veröffentlicht wird.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
