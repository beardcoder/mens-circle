<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_published')
                    ->label('Veröffentlicht')
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label('Veröffentlichungsdatum'),

                Builder::make('content_blocks')
                    ->label('Inhaltsblöcke')
                    ->blocks([
                        Builder\Block::make('hero')
                            ->label('Hero Bereich')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('description')
                                    ->label('Beschreibung')
                                    ->rows(3),
                                TextInput::make('button_text')
                                    ->label('Button Text'),
                                TextInput::make('button_link')
                                    ->label('Button Link'),
                                FileUpload::make('background_image')
                                    ->label('Hintergrundbild')
                                    ->disk('public')
                                    ->image(),
                            ]),

                        Builder\Block::make('intro')
                            ->label('Intro Bereich')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('text')
                                    ->label('Text')
                                    ->rows(3),
                                Textarea::make('quote')
                                    ->label('Zitat (HTML erlaubt)')
                                    ->rows(2),
                                Repeater::make('values')
                                    ->label('Werte')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Nummer'),
                                        TextInput::make('title')
                                            ->label('Titel')
                                            ->required(),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),

                        Builder\Block::make('text_section')
                            ->label('Text Bereich')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                TextInput::make('title')
                                    ->label('Titel')
                                    ->required(),
                                RichEditor::make('content')
                                    ->label('Inhalt')
                                    ->required(),
                            ]),

                        Builder\Block::make('value_items')
                            ->label('Werte Liste')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                TextInput::make('title')
                                    ->label('Titel'),
                                Repeater::make('items')
                                    ->label('Werte')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Nummer')
                                            ->numeric(),
                                        TextInput::make('title')
                                            ->label('Titel')
                                            ->required(),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),

                        Builder\Block::make('moderator')
                            ->label('Moderator Bereich')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('name')
                                    ->label('Name (HTML erlaubt für <span class="light">)')
                                    ->required()
                                    ->rows(2),
                                RichEditor::make('bio')
                                    ->label('Biografie')
                                    ->required(),
                                Textarea::make('quote')
                                    ->label('Zitat')
                                    ->rows(3),
                                FileUpload::make('photo')
                                    ->label('Foto')
                                    ->disk('public')
                                    ->image(),
                            ]),

                        Builder\Block::make('journey_steps')
                            ->label('Ablauf Schritte')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->rows(2),
                                Textarea::make('subtitle')
                                    ->label('Untertitel')
                                    ->rows(2),
                                Repeater::make('steps')
                                    ->label('Schritte')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Nummer')
                                            ->numeric(),
                                        TextInput::make('title')
                                            ->label('Titel')
                                            ->required(),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),

                        Builder\Block::make('faq')
                            ->label('FAQ Bereich')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->rows(2),
                                Textarea::make('intro')
                                    ->label('Intro Text')
                                    ->rows(2),
                                Repeater::make('items')
                                    ->label('Fragen & Antworten')
                                    ->schema([
                                        TextInput::make('question')
                                            ->label('Frage')
                                            ->required(),
                                        Textarea::make('answer')
                                            ->label('Antwort')
                                            ->required()
                                            ->rows(3),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                            ]),

                        Builder\Block::make('newsletter')
                            ->label('Newsletter Bereich')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('text')
                                    ->label('Text')
                                    ->rows(2),
                            ]),

                        Builder\Block::make('cta')
                            ->label('Call-to-Action')
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('text')
                                    ->label('Text')
                                    ->rows(2),
                                TextInput::make('button_text')
                                    ->label('Button Text'),
                                TextInput::make('button_link')
                                    ->label('Button Link')
                                    ->url(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                KeyValue::make('meta')
                    ->label('SEO Meta Tags')
                    ->keyLabel('Schlüssel')
                    ->valueLabel('Wert')
                    ->columnSpanFull(),
            ]);
    }
}
