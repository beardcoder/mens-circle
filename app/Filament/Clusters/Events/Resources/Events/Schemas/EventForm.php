<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grundinformationen')
                    ->description('Titel, Beschreibung und Bild für das Event')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('z.B. Männerabend im Januar')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', null)),
                        TextInput::make('slug')
                            ->label('URL-Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Wird automatisch aus dem Titel generiert'),
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(4)
                            ->placeholder('Beschreibe das Event...')
                            ->helperText('Kurze Beschreibung für die Event-Übersicht'),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Event-Bild')
                            ->image()
                            ->collection('event_image')
                            ->disk('public')
                            ->responsiveImages()
                            ->imageEditor()
                            ->helperText('Empfohlen: 16:9 Format, mindestens 1200x675px'),
                    ]),

                Section::make('Datum & Zeit')
                    ->description('Wann findet das Event statt?')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('event_date')
                            ->label('Veranstaltungsdatum')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->minDate(now()),
                        TimePicker::make('start_time')
                            ->label('Startzeit')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),
                        TimePicker::make('end_time')
                            ->label('Endzeit')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),
                    ]),

                Section::make('Ort')
                    ->description('Wo findet das Event statt?')
                    ->columns(3)
                    ->schema([
                        TextInput::make('location')
                            ->label('Ort/Veranstaltungsort')
                            ->required()
                            ->default('Straubing')
                            ->maxLength(255)
                            ->placeholder('z.B. Gemeindehaus St. Jakob')
                            ->columnSpanFull(),
                        TextInput::make('street')
                            ->label('Straße & Hausnummer')
                            ->maxLength(255)
                            ->placeholder('z.B. Hauptstraße 1')
                            ->columnSpan(2),
                        TextInput::make('postal_code')
                            ->label('PLZ')
                            ->maxLength(10)
                            ->placeholder('z.B. 94315')
                            ->columnSpan(1),
                        TextInput::make('city')
                            ->label('Stadt')
                            ->maxLength(255)
                            ->placeholder('z.B. Straubing')
                            ->default('Straubing')
                            ->columnSpanFull(),
                        Textarea::make('location_details')
                            ->label('Ortsdetails für Teilnehmer')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Zusätzliche Informationen wie Parkplatz, Eingang, etc.')
                            ->helperText('Wird nur angemeldeten Teilnehmern angezeigt'),
                    ]),

                Section::make('Teilnehmer & Kosten')
                    ->description('Teilnehmerbegrenzung und Kostenbasis')
                    ->columns(2)
                    ->schema([
                        TextInput::make('max_participants')
                            ->label('Maximale Teilnehmer')
                            ->required()
                            ->numeric()
                            ->default(8)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Anzahl der verfügbaren Plätze'),
                        TextInput::make('cost_basis')
                            ->label('Kostenbasis')
                            ->required()
                            ->default('Auf Spendenbasis')
                            ->maxLength(255)
                            ->placeholder('z.B. Kostenlos, 10€, Auf Spendenbasis'),
                    ]),

                Section::make('Veröffentlichung')
                    ->description('Sichtbarkeit auf der Website')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Event veröffentlichen')
                            ->default(false)
                            ->helperText('Aktivieren, um das Event auf der Website anzuzeigen')
                            ->inline(false),
                    ]),
            ]);
    }
}
