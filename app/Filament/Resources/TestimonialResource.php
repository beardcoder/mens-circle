<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Erfahrungsbericht';

    protected static ?string $pluralModelLabel = 'Erfahrungsberichte';

    protected static UnitEnum|string|null $navigationGroup = 'Inhalte';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Testimonial-Inhalt')
                    ->description('Das Zitat des Teilnehmers')
                    ->schema([
                        Textarea::make('quote')
                            ->label('Zitat')
                            ->required()
                            ->rows(6)
                            ->maxLength(1000)
                            ->placeholder('Schreibe hier das Testimonial...')
                            ->helperText('Das Testimonial-Zitat des Teilnehmers (max. 1000 Zeichen).'),
                    ]),

                Section::make('Autor-Informationen')
                    ->description('Informationen über den Autor (optional)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Name')
                            ->maxLength(255)
                            ->placeholder('z.B. Max Mustermann')
                            ->helperText('Optional: Name des Teilnehmers. Leer lassen für anonyme Zitate.'),
                        TextInput::make('role')
                            ->label('Rolle/Beschreibung')
                            ->maxLength(255)
                            ->placeholder('z.B. Teilnehmer seit 2023')
                            ->helperText('Zusätzliche Beschreibung zum Autor.'),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('max@beispiel.de')
                            ->helperText('Wird nicht veröffentlicht, nur für interne Rückfragen.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Anzeigeoptionen')
                    ->description('Sortierung und Veröffentlichung')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sortierung')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Niedrigere Zahlen erscheinen zuerst.'),
                        Toggle::make('is_published')
                            ->label('Veröffentlicht')
                            ->default(false)
                            ->helperText('Testimonial auf der Website anzeigen.')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state) {
                                    $set('published_at', now());
                                }
                            }),
                        DateTimePicker::make('published_at')
                            ->label('Veröffentlichungsdatum')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Wird automatisch gesetzt, wenn das Testimonial veröffentlicht wird.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote')
                    ->label('Zitat')
                    ->limit(60)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('author_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Anonym'),

                TextColumn::make('role')
                    ->label('Rolle')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sortierung')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Testimonial>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
