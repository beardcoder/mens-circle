<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titel')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Beschreibung')
                    ->rows(4)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Event-Bild')
                    ->image()
                    ->directory('events')
                    ->visibility('public')
                    ->imageEditor()
                    ->columnSpanFull()
                    ->helperText('Optionales Bild für die Event-Detailseite (empfohlen: 16:9 Format)'),
                DateTimePicker::make('event_date')
                    ->label('Veranstaltungsdatum')
                    ->required()
                    ->native(false),
                TimePicker::make('start_time')
                    ->label('Startzeit')
                    ->required()
                    ->seconds(false),
                TimePicker::make('end_time')
                    ->label('Endzeit')
                    ->required()
                    ->seconds(false),
                TextInput::make('location')
                    ->label('Ort')
                    ->required()
                    ->default('Straubing')
                    ->maxLength(255),
                Textarea::make('location_details')
                    ->label('Ortsdetails (nach Anmeldung)')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Diese Details werden nur registrierten Teilnehmern angezeigt.'),
                TextInput::make('max_participants')
                    ->label('Max. Teilnehmer')
                    ->required()
                    ->numeric()
                    ->default(8)
                    ->minValue(1),
                TextInput::make('cost_basis')
                    ->label('Kostenbasis')
                    ->required()
                    ->default('Auf Spendenbasis')
                    ->maxLength(255),
                Toggle::make('is_published')
                    ->label('Veröffentlicht')
                    ->default(false),
            ]);
    }
}
