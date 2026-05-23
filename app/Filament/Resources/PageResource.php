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
use Filament\Forms\Components\Select;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    protected static UnitEnum|string|null $navigationGroup = 'Inhalte';

    protected static ?int $navigationSort = 40;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('slug')->maxLength(255)->unique(ignoreRecord: true),
            Toggle::make('is_published')->label('Veröffentlicht')->default(false),
            DateTimePicker::make('published_at')->label('Veröffentlichungsdatum'),

            self::contentBlocksBuilder(),

            KeyValue::make('meta')->label('SEO Meta Tags')->keyLabel('Schlüssel')->valueLabel('Wert')->columnSpanFull(),
        ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titel')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                IconColumn::make('is_published')->label('Veröffentlicht')->boolean()->sortable(),
                TextColumn::make('published_at')->label('Veröffentlicht am')->dateTime('d.m.Y H:i')->sortable(),
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
            ->filters([TrashedFilter::make()])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    #[\Override]
    public static function getRecordRouteBindingEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    private static function contentBlocksBuilder(): Builder
    {
        return Builder::make('content_blocks')
            ->label('Inhaltsblöcke')
            ->collapsible()
            ->collapsed()
            ->afterStateUpdated(static function (Builder $component): void {
                $state = $component->getState();

                if (!\is_array($state)) {
                    return;
                }

                /** @var array<string, true> $seenBlockIds */
                $seenBlockIds = [];
                $hasUpdates = false;

                foreach ($state as $key => $item) {
                    if (!\is_array($item)) {
                        continue;
                    }

                    $data = $item['data'] ?? [];
                    if (!\is_array($data)) {
                        continue;
                    }

                    $blockId = $data['block_id'] ?? null;
                    if (!\is_string($blockId)) {
                        $blockId = null;
                    }

                    if (!$blockId || \array_key_exists($blockId, $seenBlockIds)) {
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
                self::pageHeroBlock(),
                self::introBlock(),
                self::textSectionBlock(),
                self::valueItemsBlock(),
                self::archetypesBlock(),
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
                self::anchorField(),
                TextInput::make('label')->label('Label (klein)'),
                self::titleTextarea(),
                Textarea::make('description')->label('Beschreibung')->rows(3),
                ...self::buttonFields(),
                self::blockImageUpload('background_image', 'Hintergrundbild'),
            ]);
    }

    /**
     * Compact hero variant for subpages: H1, optional eyebrow, optional
     * lead paragraph, optional accent image and optional CTA button.
     */
    private static function pageHeroBlock(): Block
    {
        return Block::make('page_hero')
            ->label('Unterseiten-Hero')
            ->icon(Heroicon::OutlinedFlag)
            ->schema([
                self::blockIdField(),
                self::anchorField(),
                self::eyebrowField(),
                self::titleTextarea(),
                Textarea::make('lead')->label('Lead-Text')->rows(3)->helperText('Kurzer Einleitungstext unter dem Titel.'),
                Select::make('align')
                    ->label('Ausrichtung')
                    ->options(['center' => 'Zentriert', 'left' => 'Links'])
                    ->default('center')
                    ->native(false),
                ...self::buttonFields(),
                self::blockImageUpload('image', 'Bild (optional)'),
            ]);
    }

    private static function introBlock(): Block
    {
        return Block::make('intro')
            ->label('Intro Bereich')
            ->icon(Heroicon::OutlinedInformationCircle)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'ueber'),
                self::eyebrowField(),
                self::titleTextarea(),
                Textarea::make('text')->label('Text')->rows(3),
                Textarea::make('quote')->label('Zitat (HTML erlaubt)')->rows(2),
                self::numberTitleDescriptionRepeater('values', 'Werte', numeric: false),
            ]);
    }

    private static function textSectionBlock(): Block
    {
        return Block::make('text_section')
            ->label('Text Bereich')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                self::blockIdField(),
                self::anchorField(),
                self::eyebrowField(),
                TextInput::make('title')->label('Titel')->required(),
                RichEditor::make('content')->label('Inhalt')->required(),
            ]);
    }

    private static function valueItemsBlock(): Block
    {
        return Block::make('value_items')
            ->label('Werte Liste')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->schema([
                self::blockIdField(),
                self::anchorField(),
                self::eyebrowField(),
                TextInput::make('title')->label('Titel'),
                self::numberTitleDescriptionRepeater('items', 'Werte'),
            ]);
    }

    private static function archetypesBlock(): Block
    {
        return Block::make('archetypes')
            ->label('Archetypen Kompass')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'archetypen'),
                self::eyebrowField(),
                TextInput::make('title')->label('Titel')->required(),
                Textarea::make('intro')->label('Intro Text')->rows(2),
                self::numberTitleDescriptionRepeater('items', 'Archetypen', defaultItems: 5),
            ]);
    }

    private static function moderatorBlock(): Block
    {
        return Block::make('moderator')
            ->label('Moderator Bereich')
            ->icon(Heroicon::OutlinedUserCircle)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'moderator'),
                self::eyebrowField(),
                Textarea::make('name')->label('Name (HTML erlaubt für <span class="light">)')->required()->rows(2),
                RichEditor::make('bio')->label('Biografie')->required(),
                Textarea::make('quote')->label('Zitat')->rows(3),
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
                self::anchorField(default: 'reise'),
                self::eyebrowField(),
                self::titleTextarea(required: false),
                Textarea::make('subtitle')->label('Untertitel')->rows(2),
                self::numberTitleDescriptionRepeater('steps', 'Schritte'),
            ]);
    }

    private static function testimonialsBlock(): Block
    {
        return Block::make('testimonials')
            ->label('Testimonials Bereich')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'stimmen'),
                TextEntry::make('testimonials_info')
                    ->label('Automatische Anzeige')
                    ->state(
                        'Dieser Block zeigt automatisch alle veröffentlichten Testimonials aus der Datenbank an. Keine weiteren Einstellungen erforderlich.',
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
                self::anchorField(default: 'faq'),
                self::eyebrowField(),
                self::titleTextarea(required: false),
                Textarea::make('intro')->label('Intro Text')->rows(2),
                Repeater::make('items')
                    ->label('Fragen & Antworten')
                    ->schema([
                        TextInput::make('question')->label('Frage')->required(),
                        Textarea::make('answer')->label('Antwort')->required()->rows(3),
                    ])
                    ->collapsible()
                    ->itemLabel(static fn(array $state): ?string => $state['question'] ?? null),
            ]);
    }

    private static function newsletterBlock(): Block
    {
        return Block::make('newsletter')
            ->label('Newsletter Bereich')
            ->icon(Heroicon::OutlinedEnvelope)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'newsletter'),
                self::eyebrowField(),
                self::titleTextarea(),
                Textarea::make('text')->label('Text')->rows(2),
            ]);
    }

    private static function ctaBlock(): Block
    {
        return Block::make('cta')
            ->label('Call-to-Action')
            ->icon(Heroicon::OutlinedCursorArrowRipple)
            ->schema([
                self::blockIdField(),
                self::anchorField(),
                self::eyebrowField(),
                self::titleTextarea(),
                Textarea::make('text')->label('Text')->rows(2),
                ...self::buttonFields(),
            ]);
    }

    private static function whatsappCommunityBlock(): Block
    {
        return Block::make('whatsapp_community')
            ->label('WhatsApp Community')
            ->icon(Heroicon::OutlinedDevicePhoneMobile)
            ->schema([
                self::blockIdField(),
                self::anchorField(default: 'whatsapp-community'),
                TextEntry::make('whatsapp_info')
                    ->label('Automatische Anzeige')
                    ->state(
                        'Dieser Block zeigt die WhatsApp Community Sektion an. Die WhatsApp Community URL wird in den allgemeinen Einstellungen konfiguriert.',
                    ),
            ]);
    }

    private static function blockIdField(): Hidden
    {
        return Hidden::make('block_id')->default(static fn(): string => (string) Str::uuid());
    }

    /**
     * Optional anchor used as the rendered section id and target for
     * NavigationItem links. Stored inside the block's data array.
     */
    private static function anchorField(?string $default = null): TextInput
    {
        $field = TextInput::make('anchor')
            ->label('Anker (ID)')
            ->maxLength(255)
            ->placeholder('ueber, faq, stimmen, ...')
            ->dehydrateStateUsing(self::normaliseAnchor(...))
            ->helperText(
                'Optionaler Anker für die Sektion. Wird als id-Attribut gesetzt und kann in der Navigation verlinkt werden. Ohne führendes "#" eingeben.',
            );

        return $default === null ? $field : $field->default($default);
    }

    /**
     * Normalise anchor input: strip whitespace, leading "#" and slugify
     * to a safe HTML id. Returns null for empty values.
     */
    private static function normaliseAnchor(?string $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $value = ltrim(trim($state), '#');

        if ($value === '') {
            return null;
        }

        return Str::slug($value);
    }

    private static function eyebrowField(): TextInput
    {
        return TextInput::make('eyebrow')->label('Überschrift (klein)');
    }

    private static function titleTextarea(bool $required = true): Textarea
    {
        $field = Textarea::make('title')->label('Titel (HTML erlaubt)')->rows(2);

        return $required ? $field->required() : $field;
    }

    /**
     * @return list<TextInput>
     */
    private static function buttonFields(): array
    {
        return [
            TextInput::make('button_text')->label('Button Text'),
            TextInput::make('button_link')->label('Button Link'),
        ];
    }

    private static function numberTitleDescriptionRepeater(
        string $name,
        string $label,
        bool $numeric = true,
        ?int $defaultItems = null,
    ): Repeater {
        $number = TextInput::make('number')->label('Nummer');

        $repeater = Repeater::make($name)
            ->label($label)
            ->schema([
                $numeric ? $number->numeric() : $number,
                TextInput::make('title')->label('Titel')->required(),
                Textarea::make('description')->label('Beschreibung')->rows(2),
            ])
            ->collapsible()
            ->itemLabel(static fn(array $state): ?string => $state['title'] ?? null);

        return $defaultItems !== null ? $repeater->defaultItems($defaultItems) : $repeater;
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
            ->customProperties(static fn(Get $get): array => [
                'block_id' => $get('block_id'),
                'field' => $name,
            ])
            ->filterMediaUsing(static function (Collection $media, Get $get) use ($name): Collection {
                $blockId = $get('block_id');

                if (!$blockId) {
                    return $media->take(0);
                }

                return $media->where('custom_properties.block_id', $blockId)->where('custom_properties.field', $name);
            });
    }
}
