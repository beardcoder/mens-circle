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
                self::generalSection(),
                self::contentBlocksSection(),
                self::seoSection(),
            ]);
    }

    private static function generalSection(): Section
    {
        return Section::make('Allgemein')
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
            ]);
    }

    private static function contentBlocksSection(): Section
    {
        return Section::make('Inhaltsblöcke')
            ->columnSpanFull()
            ->schema([
                Repeater::make('contentBlocks')
                    ->relationship('contentBlocks')
                    ->orderColumn('order')
                    ->columnSpanFull()
                    ->label('')
                    ->schema(self::contentBlockFields())
                    ->mutateRelationshipDataBeforeCreateUsing(self::ensureDataArray(...))
                    ->mutateRelationshipDataBeforeSaveUsing(self::ensureDataArray(...))
                    ->collapsible()
                    ->collapsed()
                    ->cloneable()
                    ->itemLabel(fn (array $state): ?string => self::getBlockItemLabel($state))
                    ->columnSpanFull()
                    ->reorderable()
                    ->addActionLabel('Block hinzufügen'),
            ]);
    }

    private static function seoSection(): Section
    {
        return Section::make('SEO')
            ->schema([
                KeyValue::make('meta')
                    ->label('Meta Tags')
                    ->keyLabel('Schlüssel')
                    ->valueLabel('Wert')
                    ->columnSpanFull(),
            ])
            ->collapsed();
    }

    private static function contentBlockFields(): array
    {
        return [
            // Block-Typ Auswahl
            Select::make('type')
                ->label('Block-Typ')
                ->options(ContentBlockType::class)
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) => $set('data', [])),

            // Text-Felder
            TextInput::make('data.eyebrow')
                ->label('Überschrift (klein)')
                ->visible(fn (Get $get) => self::getType($get)?->hasEyebrow() ?? false),

            TextInput::make('data.label')
                ->label('Label (klein)')
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Hero)),

            Textarea::make('data.title')
                ->label(fn (Get $get) => self::getType($get)?->hasHtmlTitle() ? 'Titel (HTML erlaubt)' : 'Titel')
                ->rows(fn (Get $get) => self::getType($get)?->hasSmallTitle() ? 1 : 2)
                ->visible(fn (Get $get) => self::getType($get)?->hasTitle() ?? false),

            Textarea::make('data.name')
                ->label('Name (HTML erlaubt für <span class="light">)')
                ->rows(2)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Moderator)),

            Textarea::make('data.description')
                ->label('Beschreibung')
                ->rows(3)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Hero)),

            Textarea::make('data.text')
                ->label('Text')
                ->rows(fn (Get $get) => self::isType($get, ContentBlockType::Newsletter) ? 2 : 3)
                ->visible(fn (Get $get) => self::getType($get)?->hasText() ?? false),

            Textarea::make('data.subtitle')
                ->label('Untertitel')
                ->rows(2)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::JourneySteps)),

            Textarea::make('data.intro')
                ->label('Intro Text')
                ->rows(2)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Faq)),

            Textarea::make('data.quote')
                ->label('Zitat')
                ->rows(fn (Get $get) => self::isType($get, ContentBlockType::Intro) ? 2 : 3)
                ->helperText(fn (Get $get) => self::isType($get, ContentBlockType::Intro) ? 'HTML erlaubt' : null)
                ->visible(fn (Get $get) => self::getType($get)?->hasQuote() ?? false),

            // Rich-Text Felder
            RichEditor::make('data.content')
                ->label('Inhalt')
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::TextSection)),

            RichEditor::make('data.bio')
                ->label('Biografie')
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Moderator)),

            // Button-Felder
            TextInput::make('data.button_text')
                ->label('Button Text')
                ->visible(fn (Get $get) => self::getType($get)?->hasButton() ?? false),

            TextInput::make('data.button_link')
                ->label('Button Link')
                ->visible(fn (Get $get) => self::getType($get)?->hasButton() ?? false),

            // Bild-Upload
            SpatieMediaLibraryFileUpload::make('images')
                ->label(fn (Get $get) => self::isType($get, ContentBlockType::Hero) ? 'Hintergrundbild' : 'Foto')
                ->collection('images')
                ->disk('public')
                ->image()
                ->imageEditor()
                ->responsiveImages()
                ->maxFiles(1)
                ->visible(fn (Get $get) => self::getType($get)?->hasImage() ?? false),

            // Repeater-Felder
            Repeater::make('data.values')
                ->label('Werte')
                ->schema([
                    TextInput::make('number')->label('Nummer'),
                    TextInput::make('title')->label('Titel'),
                    Textarea::make('description')->label('Beschreibung')->rows(2),
                ])
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::Intro)),

            Repeater::make('data.items')
                ->label(fn (Get $get) => self::isType($get, ContentBlockType::Faq) ? 'Fragen & Antworten' : 'Werte')
                ->schema(fn (Get $get) => self::isType($get, ContentBlockType::Faq)
                    ? [
                        TextInput::make('question')->label('Frage'),
                        Textarea::make('answer')->label('Antwort')->rows(3),
                    ]
                    : [
                        TextInput::make('number')->label('Nummer')->numeric(),
                        TextInput::make('title')->label('Titel'),
                        Textarea::make('description')->label('Beschreibung')->rows(2),
                    ])
                ->collapsible()
                ->itemLabel(fn (array $state, Get $get): ?string => self::isType($get, ContentBlockType::Faq)
                    ? ($state['question'] ?? null)
                    : ($state['title'] ?? null))
                ->visible(fn (Get $get) => self::getType($get)?->hasItemsRepeater() ?? false),

            Repeater::make('data.steps')
                ->label('Schritte')
                ->schema([
                    TextInput::make('number')->label('Nummer')->numeric(),
                    TextInput::make('title')->label('Titel'),
                    Textarea::make('description')->label('Beschreibung')->rows(2),
                ])
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->visible(fn (Get $get) => self::isType($get, ContentBlockType::JourneySteps)),
        ];
    }

    private static function getType(Get $get): ?ContentBlockType
    {
        $type = $get('type');

        if ($type instanceof ContentBlockType) {
            return $type;
        }

        return $type ? ContentBlockType::tryFrom($type) : null;
    }

    private static function isType(Get $get, ContentBlockType $expectedType): bool
    {
        return self::getType($get) === $expectedType;
    }

    private static function ensureDataArray(array $data): array
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            $data['data'] = [];
        }

        return $data;
    }

    private static function getBlockItemLabel(array $state): ?string
    {
        if ($state['type'] instanceof ContentBlockType) {
            return $state['type']->labelWithIcon();
        }

        return isset($state['type'])
            ? ContentBlockType::from($state['type'])->labelWithIcon()
            : 'Unbekannter Block';
    }
}

