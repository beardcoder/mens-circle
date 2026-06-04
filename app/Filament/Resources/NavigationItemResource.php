<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Filament\Resources\NavigationItemResource\Pages\ListNavigationItems;
use App\Filament\Support\Anchor;
use App\Models\NavigationItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
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
                        ->default(static fn(mixed $livewire): ?string => $livewire instanceof ListNavigationItems
                            && is_string($livewire->activeTab)
                                ? $livewire->activeTab
                                : null)
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
                        ->dehydrateStateUsing(Anchor::normalise(...))
                        ->helperText(
                            'Optionaler Anker (z.B. "ueber"). Wird an die URL angehängt: /pfad#anker. Ohne führendes "#" eingeben.',
                        ),

                    Select::make('condition')
                        ->label('Bedingung')
                        ->options(NavigationCondition::class)
                        ->native(false)
                        ->placeholder('Keine')
                        ->helperText('Optional: Sichtbarkeit & URL dynamisch steuern.'),
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
                TextColumn::make('label')->label('Beschriftung')->searchable(),
                TextColumn::make('url')->label('URL')->limit(40)->toggleable(),
                TextColumn::make('anchor')->label('Anker')->badge()->toggleable(),
                TextColumn::make('condition')->label('Bedingung')->badge()->toggleable(),
                ToggleColumn::make('is_visible')->label('Sichtbar'),
                ToggleColumn::make('is_cta')->label('CTA'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->defaultPaginationPageOption(50)
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNavigationItems::route('/'),
        ];
    }
}
