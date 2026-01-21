<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterResource\Pages\ListNewsletters;
use App\Filament\Resources\NewsletterResource\Pages\ViewNewsletter;
use App\Models\Newsletter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static ?string $navigationLabel = 'Newsletter Archiv';

    protected static ?string $modelLabel = 'Newsletter';

    protected static ?string $pluralModelLabel = 'Newsletter';

    protected static UnitEnum|string|null $navigationGroup = 'Newsletter';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::Newspaper;

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->label('Betreff')
                    ->disabled()
                    ->dehydrated(false),
                RichEditor::make('content')
                    ->label('Inhalt')
                    ->disabled()
                    ->dehydrated(false)
                    ->toolbarButtons([]),
                TextInput::make('status')
                    ->label('Status')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('recipient_count')
                    ->label('Anzahl Empfänger')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric(),
                DateTimePicker::make('sent_at')
                    ->label('Versendet am')
                    ->disabled()
                    ->dehydrated(false)
                    ->displayFormat('d.m.Y H:i'),
                DateTimePicker::make('created_at')
                    ->label('Erstellt am')
                    ->disabled()
                    ->dehydrated(false)
                    ->displayFormat('d.m.Y H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('Betreff')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'sending' => 'warning',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent' => 'Versendet',
                        'sending' => 'Wird versendet',
                        'draft' => 'Entwurf',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('recipient_count')
                    ->label('Empfänger')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Versendet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'sending' => 'Wird versendet',
                        'sent' => 'Versendet',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListNewsletters::route('/'),
            'view' => ViewNewsletter::route('/{record}'),
        ];
    }
}
