<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationLabel = 'Seiten';

    protected static ?string $modelLabel = 'Seite';

    protected static ?string $pluralModelLabel = 'Seiten';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::Document;

    protected static UnitEnum|string|null $navigationGroup = 'Inhalte';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
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

                self::contentBlocksBuilder(),

                KeyValue::make('meta')
                    ->label('SEO Meta Tags')
                    ->keyLabel('Schlüssel')
                    ->valueLabel('Wert')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Veröffentlicht am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([TrashedFilter::make(), ])
            ->recordActions([EditAction::make(), ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Page>
     */
    public static function getRecordRouteBindingEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class, ]);
    }

    private static function contentBlocksBuilder(): Builder
    {
        return Builder::make('content_blocks')
            ->label('Inhaltsblöcke')
            ->collapsible()
            ->collapsed()
            ->afterStateUpdated(function (Builder $component): void {
                $state = $component->getState();

                if (!is_array($state)) {
                    return;
                }

                $seenBlockIds = [];
                $hasUpdates = false;

                foreach ($state as $key => $item) {
                    $data = $item['data'] ?? [];
                    $blockId = $data['block_id'] ?? null;

                    if (!$blockId || array_key_exists($blockId, $seenBlockIds)) {
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
                self::heroBlock(),
                self::introBlock(),
                self::textSectionBlock(),
                self::valueItemsBlock(),
                self::moderatorBlock(),
                self::journeyStepsBlock(),
                self::testimonialsBlock(),
                self::faqBlock(),
                self::newsletterBlock(),
                self::ctaBlock(),
                self::whatsappCommunityBlock(),
            ])
            ->columnSpanFull()
            ->collapsible();
    }

    private static function heroBlock(): Block
    {
        return Block::make('hero')
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
            ]);
    }

    private static function introBlock(): Block
    {
        return Block::make('intro')
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
            ]);
    }

    private static function textSectionBlock(): Block
    {
        return Block::make('text_section')
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
            ]);
    }

    private static function valueItemsBlock(): Block
    {
        return Block::make('value_items')
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
            ]);
    }

    private static function moderatorBlock(): Block
    {
        return Block::make('moderator')
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
            ]);
    }

    private static function journeyStepsBlock(): Block
    {
        return Block::make('journey_steps')
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
            ]);
    }

    private static function testimonialsBlock(): Block
    {
        return Block::make('testimonials')
            ->label('Testimonials Bereich')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->schema([
                self::blockIdField(),
                TextEntry::make('testimonials_info')
                    ->label('Automatische Anzeige')
                    ->state(
                        'Dieser Block zeigt automatisch alle veröffentlichten Testimonials aus der Datenbank an. Keine weiteren Einstellungen erforderlich.'
                    ),
            ]);
    }

    private static function faqBlock(): Block
    {
        return Block::make('faq')
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
            ]);
    }

    private static function newsletterBlock(): Block
    {
        return Block::make('newsletter')
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
            ]);
    }

    private static function ctaBlock(): Block
    {
        return Block::make('cta')
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
            ]);
    }

    private static function whatsappCommunityBlock(): Block
    {
        return Block::make('whatsapp_community')
            ->label('WhatsApp Community')
            ->icon(Heroicon::OutlinedDevicePhoneMobile)
            ->schema([
                self::blockIdField(),
                TextEntry::make('whatsapp_info')
                    ->label('Automatische Anzeige')
                    ->state(
                        'Dieser Block zeigt die WhatsApp Community Sektion an. Die WhatsApp Community URL wird in den allgemeinen Einstellungen konfiguriert.'
                    ),
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

                if (!$blockId) {
                    return $media->take(0);
                }

                return $media
                    ->where('custom_properties.block_id', $blockId)
                    ->where('custom_properties.field', $name);
            });
    }
}
