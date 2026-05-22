<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationType;
use App\Filament\Resources\NavigationResource\Pages;
use App\Models\Navigation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class NavigationResource extends Resource
{
    protected static ?string $model = Navigation::class;

    protected static string|Heroicon|null $navigationIcon = Heroicon::Bars3;

    protected static string|\UnitEnum|null $navigationGroup = 'Inhalt';

    protected static ?string $modelLabel = 'Navigation';

    protected static ?string $pluralModelLabel = 'Navigationen';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Navigation Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('type')
                                    ->label('Typ')
                                    ->options(NavigationType::class)
                                    ->required()
                                    ->rules([
                                        fn (Forms\Get $get, ?Navigation $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                            $existingNav = Navigation::where('type', $value)
                                                ->where('is_active', true)
                                                ->when($record, fn($query) => $query->where('id', '!=', $record->id))
                                                ->first();

                                            if ($existingNav) {
                                                $fail("Eine aktive Navigation vom Typ '{$value}' existiert bereits: {$existingNav->name}");
                                            }
                                        },
                                    ])
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Navigation Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('label')
                                            ->label('Label')
                                            ->required()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('url')
                                            ->label('URL')
                                            ->url()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('route_name')
                                            ->label('Route Name')
                                            ->helperText('z.B. home, page.show')
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('anchor')
                                            ->label('Anker')
                                            ->helperText('z.B. ueber, faq (ohne #)')
                                            ->columnSpan(1),

                                        Forms\Components\Select::make('target')
                                            ->label('Ziel')
                                            ->options([
                                                '_self' => 'Gleiches Fenster',
                                                '_blank' => 'Neues Fenster',
                                                '_parent' => 'Eltern-Frame',
                                                '_top' => 'Oberstes Frame',
                                            ])
                                            ->default('_self')
                                            ->in(['_self', '_blank', '_parent', '_top'])
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('icon')
                                            ->label('Icon')
                                            ->helperText('CSS-Klasse oder Icon-Name')
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('css_class')
                                            ->label('CSS Klasse')
                                            ->helperText('Zusätzliche CSS-Klassen')
                                            ->columnSpan(1),

                                        Forms\Components\KeyValue::make('route_params')
                                            ->label('Route Parameter')
                                            ->helperText('z.B. slug => impressum')
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\KeyValue::make('data_attributes')
                                    ->label('Data Attribute')
                                    ->helperText('z.B. umami-event => nav-click')
                                    ->columnSpanFull(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktiv')
                                    ->default(true),
                            ])
                            ->reorderable('order')
                            ->orderColumn('order')
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Navigation Item hinzufügen'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Typ')
                    ->options(NavigationType::class),

                Tables\Filters\Filter::make('is_active')
                    ->label('Nur aktive')
                    ->query(fn($query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavigations::route('/'),
            'create' => Pages\CreateNavigation::route('/create'),
            'edit' => Pages\EditNavigation::route('/{record}/edit'),
        ];
    }
}
