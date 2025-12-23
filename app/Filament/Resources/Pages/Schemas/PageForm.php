<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ContentBlockType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
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

                Repeater::make('contentBlocks')
                    ->relationship('contentBlocks')
                    ->orderColumn('order')
                    ->collapsible()
                    ->collapsed()
                    ->label('Inhaltsblöcke')
                    ->schema([
                        Select::make('type')
                            ->label('Block-Typ')
                            ->options([
                                'hero' => 'Hero Bereich',
                                'intro' => 'Intro Bereich',
                                'text_section' => 'Text Bereich',
                                'value_items' => 'Werte Liste',
                                'moderator' => 'Moderator Bereich',
                                'journey_steps' => 'Ablauf Schritte',
                                'faq' => 'FAQ Bereich',
                                'newsletter' => 'Newsletter Bereich',
                                'cta' => 'Call-to-Action',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('data', [])),

                        // Hero Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.label')
                                    ->label('Label (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('data.description')
                                    ->label('Beschreibung')
                                    ->rows(3),
                                TextInput::make('data.button_text')
                                    ->label('Button Text'),
                                TextInput::make('data.button_link')
                                    ->label('Button Link'),
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->label('Hintergrundbild')
                                    ->collection('images')
                                    ->image()
                                    ->maxFiles(1),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Hero->value),

                        // Intro Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('data.text')
                                    ->label('Text')
                                    ->rows(3),
                                Textarea::make('data.quote')
                                    ->label('Zitat (HTML erlaubt)')
                                    ->rows(2),
                                Repeater::make('data.values')
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
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Intro->value),

                        // Text Section Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                TextInput::make('data.title')
                                    ->label('Titel')
                                    ->required(),
                                RichEditor::make('data.content')
                                    ->label('Inhalt')
                                    ->required(),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::TextSection->value),

                        // Value Items Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                TextInput::make('data.title')
                                    ->label('Titel'),
                                Repeater::make('data.items')
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
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::ValueItems->value),

                        // Moderator Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.name')
                                    ->label('Name (HTML erlaubt für <span class="light">)')
                                    ->required()
                                    ->rows(2),
                                RichEditor::make('data.bio')
                                    ->label('Biografie')
                                    ->required(),
                                Textarea::make('data.quote')
                                    ->label('Zitat')
                                    ->rows(3),
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->label('Foto')
                                    ->collection('images')
                                    ->image()
                                    ->maxFiles(1),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Moderator->value),

                        // Journey Steps Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->rows(2),
                                Textarea::make('data.subtitle')
                                    ->label('Untertitel')
                                    ->rows(2),
                                Repeater::make('data.steps')
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
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::JourneySteps->value),

                        // FAQ Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->rows(2),
                                Textarea::make('data.intro')
                                    ->label('Intro Text')
                                    ->rows(2),
                                Repeater::make('data.items')
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
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Faq->value),

                        // Newsletter Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('data.text')
                                    ->label('Text')
                                    ->rows(2),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Newsletter->value),

                        // CTA Block
                        Group::make()
                            ->schema([
                                TextInput::make('data.eyebrow')
                                    ->label('Überschrift (klein)'),
                                Textarea::make('data.title')
                                    ->label('Titel (HTML erlaubt)')
                                    ->required()
                                    ->rows(2),
                                Textarea::make('data.text')
                                    ->label('Text')
                                    ->rows(2),
                                TextInput::make('data.button_text')
                                    ->label('Button Text'),
                                TextInput::make('data.button_link')
                                    ->label('Button Link'),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ContentBlockType::Cta->value),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => match ($state['type'] ?? null) {
                        'hero' => '🎭 Hero Bereich',
                        'intro' => '👋 Intro Bereich',
                        'text_section' => '📝 Text Bereich',
                        'value_items' => '⭐ Werte Liste',
                        'moderator' => '👤 Moderator Bereich',
                        'journey_steps' => '🚀 Ablauf Schritte',
                        'faq' => '❓ FAQ Bereich',
                        'newsletter' => '📧 Newsletter Bereich',
                        'cta' => '📣 Call-to-Action',
                        default => 'Unbekannter Block',
                    })
                    ->columnSpanFull()
                    ->reorderable()
                    ->addActionLabel('Block hinzufügen'),

                KeyValue::make('meta')
                    ->label('SEO Meta Tags')
                    ->keyLabel('Schlüssel')
                    ->valueLabel('Wert')
                    ->columnSpanFull(),
            ]);
    }
}
