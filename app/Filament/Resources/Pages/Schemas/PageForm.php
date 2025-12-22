<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ContentBlockType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Allgemein')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
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
                    ]),

                Section::make('Inhaltsblöcke')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('contentBlocks')
                            ->relationship('contentBlocks')
                            ->orderColumn('order')
                            ->columnSpanFull()
                            ->label('')
                            ->schema([
                                Select::make('type')
                                    ->label('Block-Typ')
                                    ->options(ContentBlockType::class)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('data', [])),

                                // Eyebrow field
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Intro,
                                        ContentBlockType::TextSection,
                                        ContentBlockType::ValueItems,
                                        ContentBlockType::Moderator,
                                        ContentBlockType::JourneySteps,
                                        ContentBlockType::Faq,
                                        ContentBlockType::Newsletter,
                                        ContentBlockType::Cta,
                                    ])),

                                // Label field (Hero only)
                                TextInput::make('data.label')
                                    ->label('Label (klein)')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Hero),

                                // Title field (all types that need title)
                                Textarea::make('data.title')
                                    ->label(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero,
                                        ContentBlockType::Intro,
                                        ContentBlockType::JourneySteps,
                                        ContentBlockType::Faq,
                                        ContentBlockType::Newsletter,
                                        ContentBlockType::Cta,
                                    ]) ? 'Titel (HTML erlaubt)' : 'Titel')
                                    ->rows(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::TextSection,
                                        ContentBlockType::ValueItems,
                                    ]) ? 1 : 2)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero,
                                        ContentBlockType::Intro,
                                        ContentBlockType::TextSection,
                                        ContentBlockType::ValueItems,
                                        ContentBlockType::JourneySteps,
                                        ContentBlockType::Faq,
                                        ContentBlockType::Newsletter,
                                        ContentBlockType::Cta,
                                    ])),

                                // Name field (Moderator only)
                                Textarea::make('data.name')
                                    ->label('Name (HTML erlaubt für <span class="light">)')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Moderator),

                                // Description field (Hero only)
                                Textarea::make('data.description')
                                    ->label('Beschreibung')
                                    ->rows(3)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Hero),

                                // Text field
                                Textarea::make('data.text')
                                    ->label('Text')
                                    ->rows(fn (Get $get) => $get('type') === ContentBlockType::Newsletter ? 2 : 3)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Intro,
                                        ContentBlockType::Newsletter,
                                        ContentBlockType::Cta,
                                    ])),

                                // Subtitle field (JourneySteps only)
                                Textarea::make('data.subtitle')
                                    ->label('Untertitel')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::JourneySteps),

                                // Intro field (FAQ only)
                                Textarea::make('data.intro')
                                    ->label('Intro Text')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Faq),

                                // Quote field
                                Textarea::make('data.quote')
                                    ->label('Zitat')
                                    ->rows(fn (Get $get) => $get('type') === ContentBlockType::Intro ? 2 : 3)
                                    ->helperText(fn (Get $get) => $get('type') === ContentBlockType::Intro ? 'HTML erlaubt' : null)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Intro,
                                        ContentBlockType::Moderator,
                                    ])),

                                // Content field (TextSection only)
                                RichEditor::make('data.content')
                                    ->label('Inhalt')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::TextSection),

                                // Bio field (Moderator only)
                                RichEditor::make('data.bio')
                                    ->label('Biografie')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Moderator),

                                // Button Text field
                                TextInput::make('data.button_text')
                                    ->label('Button Text')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero,
                                        ContentBlockType::Cta,
                                    ])),

                                // Button Link field
                                TextInput::make('data.button_link')
                                    ->label('Button Link')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero,
                                        ContentBlockType::Cta,
                                    ])),

                                // Images field
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->label(fn (Get $get) => $get('type') === ContentBlockType::Hero ? 'Hintergrundbild' : 'Foto')
                                    ->collection('images')
                                    ->image()
                                    ->maxFiles(1)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero,
                                        ContentBlockType::Moderator,
                                    ])),

                                // Values repeater (Intro only)
                                Repeater::make('data.values')
                                    ->label('Werte')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Nummer'),
                                        TextInput::make('title')
                                            ->label('Titel'),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Intro),

                                // Items repeater (ValueItems and FAQ)
                                Repeater::make('data.items')
                                    ->label(fn (Get $get) => $get('type') === ContentBlockType::Faq ? 'Fragen & Antworten' : 'Werte')
                                    ->schema(fn (Get $get) => $get('type') === ContentBlockType::Faq ? [
                                        TextInput::make('question')
                                            ->label('Frage'),
                                        Textarea::make('answer')
                                            ->label('Antwort')
                                            ->rows(3),
                                    ] : [
                                        TextInput::make('number')
                                            ->label('Nummer')
                                            ->numeric(),
                                        TextInput::make('title')
                                            ->label('Titel'),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state, Get $get): ?string => $get('type') === ContentBlockType::Faq
                                        ? ($state['question'] ?? null)
                                        : ($state['title'] ?? null))
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::ValueItems,
                                        ContentBlockType::Faq,
                                    ])),

                                // Steps repeater (JourneySteps only)
                                Repeater::make('data.steps')
                                    ->label('Schritte')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Nummer')
                                            ->numeric(),
                                        TextInput::make('title')
                                            ->label('Titel'),
                                        Textarea::make('description')
                                            ->label('Beschreibung')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::JourneySteps),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                if (!isset($data['data']) || !is_array($data['data'])) {
                                    $data['data'] = [];
                                }

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                if (!isset($data['data']) || !is_array($data['data'])) {
                                    $data['data'] = [];
                                }

                                return $data;
                            })
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                if ($state['type'] instanceof ContentBlockType) {
                                    return $state['type']->labelWithIcon();
                                }
                                return isset($state['type']) ? ContentBlockType::from($state['type'])->labelWithIcon() : 'Unbekannter Block';
                            })
                            ->columnSpanFull()
                            ->reorderable()
                            ->addActionLabel('Block hinzufügen'),
                    ]),

                Section::make('SEO')
                    ->schema([
                        KeyValue::make('meta')
                            ->label('Meta Tags')
                            ->keyLabel('Schlüssel')
                            ->valueLabel('Wert')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
