<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
                    ->collapsible()
                    ->collapsed()
                    ->afterStateUpdated(function (Builder $component): void {
                        $state = $component->getState();

                        if (! is_array($state)) {
                            return;
                        }

                        $seenBlockIds = [];
                        $hasUpdates = false;

                        foreach ($state as $key => $item) {
                            $data = $item['data'] ?? [];
                            $blockId = $data['block_id'] ?? null;

                            if (! $blockId || array_key_exists($blockId, $seenBlockIds)) {
                                $data['block_id'] = (string) Str::uuid();
                                $item['data'] = $data;
                                $state[$key] = $item;
                                $hasUpdates = true;
                                $blockId = $data['block_id'];
                            }

                            $seenBlockIds[$blockId] = true;
                        }

                        if ($hasUpdates) {
                            $component->state($state);
                        }
                    })
                    ->blocks([
                        Builder\Block::make('hero')
                            ->label('Hero Bereich')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                self::blockIdField(),
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
                                self::blockImageUpload('background_image', 'Hintergrundbild'),
                            ]),

                        Builder\Block::make('intro')
                            ->label('Intro Bereich')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedRectangleStack)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                self::blockIdField(),
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
                                self::blockImageUpload('photo', 'Foto'),
                            ]),

                        Builder\Block::make('journey_steps')
                            ->label('Ablauf Schritte')
                            ->icon(Heroicon::OutlinedMap)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedQuestionMarkCircle)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema([
                                self::blockIdField(),
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
                            ->icon(Heroicon::OutlinedCursorArrowRipple)
                            ->schema([
                                self::blockIdField(),
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
                                    ->label('Button Link'),
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

    private static function blockIdField(): Hidden
    {
        return Hidden::make('block_id')
            ->default(fn (): string => (string) Str::uuid());
    }

    private static function blockImageUpload(string $name, string $label): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($name)
            ->label($label)
            ->collection('page_blocks')
            ->disk('public')
            ->image()
            ->imageEditor()
            ->responsiveImages()
            ->customProperties(fn (Get $get): array => [
                'block_id' => $get('block_id'),
                'field' => $name,
            ])
            ->filterMediaUsing(function (Collection $media, Get $get) use ($name): Collection {
                $blockId = $get('block_id');

                if (! $blockId) {
                    return $media->take(0);
                }

                return $media
                    ->where('custom_properties.block_id', $blockId)
                    ->where('custom_properties.field', $name);
            });
    }
}
