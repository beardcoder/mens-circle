<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Filament\Resources\NavigationItemResource\Pages\CreateNavigationItem;
use App\Filament\Resources\NavigationItemResource\Pages\EditNavigationItem;
use App\Filament\Resources\NavigationItemResource\Pages\ListNavigationItems;
use App\Models\NavigationItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Override;
use UnitEnum;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationLabel = 'Navigation';

    protected static ?string $modelLabel = 'Navigationspunkt';

    protected static ?string $pluralModelLabel = 'Navigationspunkte';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bars3;

    protected static UnitEnum|string|null $navigationGroup = 'Inhalte';

    protected static ?int $navigationSort = 45;

    protected static ?string $recordTitleAttribute = 'label';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Allgemein')
                ->schema([
                    Select::make('location')
                        ->label('Bereich')
                        ->options(NavigationLocation::class)
                        ->required()
                        ->native(false)
                        ->helperText('In welchem Navigationsbereich der Link erscheint.'),

                    TextInput::make('label')->label('Beschriftung')->required()->maxLength(255),

                    TextInput::make('url')
                        ->label('URL / Pfad')
                        ->maxLength(2048)
                        ->placeholder('/atemuebung oder https://...')
                        ->helperText(
                            'Beginnt mit "/": interner Pfad. Sonst absolute URL. Leer lassen für die Startseite. Bei Bedingung "Nächster Termin" wird die URL automatisch gesetzt.',
                        ),

                    TextInput::make('anchor')
                        ->label('Anker')
                        ->maxLength(255)
                        ->placeholder('ueber, stimmen, faq, ...')
                        ->dehydrateStateUsing(static fn(?string $state): ?string => self::normaliseAnchor($state))
                        ->helperText('Optionaler Anker (z.B. "ueber"). Wird an die URL angehängt: /pfad#anker. Ohne führendes "#" eingeben.'),

                    Select::make('condition')
                        ->label('Bedingung')
                        ->options(NavigationCondition::class)
                        ->native(false)
                        ->placeholder('Keine')
                        ->helperText('Optional: Sichtbarkeit & URL dynamisch steuern.'),

                    TextInput::make('sort')
                        ->label('Sortierung')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText('Kleinere Werte erscheinen zuerst.'),
                ])
                ->columns(2),

            Section::make('Verhalten')
                ->schema([
                    Toggle::make('is_visible')->label('Sichtbar')->default(true)->inline(false),
                    Toggle::make('is_cta')->label('Als Button (CTA) darstellen')->default(false)->inline(false),
                    Toggle::make('open_in_new_tab')->label('In neuem Tab öffnen')->default(false)->inline(false),
                    TextInput::make('umami_event_target')
                        ->label('Umami Event Target')
                        ->maxLength(255)
                        ->helperText('Optional: Wert für data-umami-event-target. Leer lassen für keinen Tracking-Wert.'),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('location')->label('Bereich')->badge()->sortable(),
                TextColumn::make('label')->label('Beschriftung')->searchable()->sortable(),
                TextColumn::make('url')->label('URL')->limit(40)->toggleable(),
                TextColumn::make('anchor')->label('Anker')->badge()->toggleable(),
                TextColumn::make('condition')->label('Bedingung')->badge()->toggleable(),
                IconColumn::make('is_cta')->label('CTA')->boolean()->sortable()->toggleable(),
                IconColumn::make('is_visible')->label('Sichtbar')->boolean()->sortable(),
                TextColumn::make('sort')->label('Sortierung')->sortable(),
            ])
            ->defaultSort('location')
            ->defaultGroup('location')
            ->reorderable('sort')
            ->defaultPaginationPageOption(50)
            ->filters([
                SelectFilter::make('location')->label('Bereich')->options(NavigationLocation::class),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    /**
     * Normalise anchor input: strip whitespace, a leading "#" and slugify
     * to match the slug-cased anchors written by PageResource.
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

    #[Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNavigationItems::route('/'),
            'create' => CreateNavigationItem::route('/create'),
            'edit' => EditNavigationItem::route('/{record}/edit'),
        ];
    }
}
