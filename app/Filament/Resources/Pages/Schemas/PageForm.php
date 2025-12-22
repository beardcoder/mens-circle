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
use Filament\Schemas\Components\Group;
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
                    ->schema([
                        Repeater::make('contentBlocks')
                            ->relationship('contentBlocks')
                            ->orderColumn('order')
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
                                        ContentBlockType::Intro->value,
                                        ContentBlockType::TextSection->value,
                                        ContentBlockType::ValueItems->value,
                                        ContentBlockType::Moderator->value,
                                        ContentBlockType::JourneySteps->value,
                                        ContentBlockType::Faq->value,
                                        ContentBlockType::Newsletter->value,
                                        ContentBlockType::Cta->value,
                                    ])),

                                // Label field (Hero only)
                                TextInput::make('data.label')
                                    ->label('Label (klein)')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Hero->value),

                                // Title field (all types that need title)
                                Textarea::make('data.title')
                                    ->label(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero->value,
                                        ContentBlockType::Intro->value,
                                        ContentBlockType::JourneySteps->value,
                                        ContentBlockType::Faq->value,
                                        ContentBlockType::Newsletter->value,
                                        ContentBlockType::Cta->value,
                                    ]) ? 'Titel (HTML erlaubt)' : 'Titel')
                                    ->rows(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::TextSection->value,
                                        ContentBlockType::ValueItems->value,
                                    ]) ? 1 : 2)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero->value,
                                        ContentBlockType::Intro->value,
                                        ContentBlockType::TextSection->value,
                                        ContentBlockType::ValueItems->value,
                                        ContentBlockType::JourneySteps->value,
                                        ContentBlockType::Faq->value,
                                        ContentBlockType::Newsletter->value,
                                        ContentBlockType::Cta->value,
                                    ])),

                                // Name field (Moderator only)
                                Textarea::make('data.name')
                                    ->label('Name (HTML erlaubt für <span class="light">)')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Moderator->value),

                                // Description field (Hero only)
                                Textarea::make('data.description')
                                    ->label('Beschreibung')
                                    ->rows(3)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Hero->value),

                                // Text field
                                Textarea::make('data.text')
                                    ->label('Text')
                                    ->rows(fn (Get $get) => $get('type') === ContentBlockType::Newsletter->value ? 2 : 3)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Intro->value,
                                        ContentBlockType::Newsletter->value,
                                        ContentBlockType::Cta->value,
                                    ])),

                                // Subtitle field (JourneySteps only)
                                Textarea::make('data.subtitle')
                                    ->label('Untertitel')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::JourneySteps->value),

                                // Intro field (FAQ only)
                                Textarea::make('data.intro')
                                    ->label('Intro Text')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Faq->value),

                                // Quote field
                                Textarea::make('data.quote')
                                    ->label('Zitat')
                                    ->rows(fn (Get $get) => $get('type') === ContentBlockType::Intro->value ? 2 : 3)
                                    ->helperText(fn (Get $get) => $get('type') === ContentBlockType::Intro->value ? 'HTML erlaubt' : null)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Intro->value,
                                        ContentBlockType::Moderator->value,
                                    ])),

                                // Content field (TextSection only)
                                RichEditor::make('data.content')
                                    ->label('Inhalt')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::TextSection->value),

                                // Bio field (Moderator only)
                                RichEditor::make('data.bio')
                                    ->label('Biografie')
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Moderator->value),

                                // Button Text field
                                TextInput::make('data.button_text')
                                    ->label('Button Text')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero->value,
                                        ContentBlockType::Cta->value,
                                    ])),

                                // Button Link field
                                TextInput::make('data.button_link')
                                    ->label('Button Link')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero->value,
                                        ContentBlockType::Cta->value,
                                    ])),

                                // Images field
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->label(fn (Get $get) => $get('type') === ContentBlockType::Hero->value ? 'Hintergrundbild' : 'Foto')
                                    ->collection('images')
                                    ->image()
                                    ->maxFiles(1)
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::Hero->value,
                                        ContentBlockType::Moderator->value,
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
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::Intro->value),

                                // Items repeater (ValueItems and FAQ)
                                Repeater::make('data.items')
                                    ->label(fn (Get $get) => $get('type') === ContentBlockType::Faq->value ? 'Fragen & Antworten' : 'Werte')
                                    ->schema(fn (Get $get) => $get('type') === ContentBlockType::Faq->value ? [
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
                                    ->itemLabel(fn (array $state, Get $get): ?string => $get('type') === ContentBlockType::Faq->value
                                        ? ($state['question'] ?? null)
                                        : ($state['title'] ?? null))
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        ContentBlockType::ValueItems->value,
                                        ContentBlockType::Faq->value,
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
                                    ->visible(fn (Get $get) => $get('type') === ContentBlockType::JourneySteps->value),
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
                            ->itemLabel(fn (array $state): ?string => isset($state['type'])
                                ? ContentBlockType::from($state['type'])->labelWithIcon()
                                : 'Unbekannter Block')
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
