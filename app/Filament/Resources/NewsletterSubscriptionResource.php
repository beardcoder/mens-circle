<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriptionResource\Pages;
use App\Models\NewsletterSubscription;
use App\Models\Participant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static ?string $navigationLabel = 'Abonnenten';

    protected static ?string $modelLabel = 'Abonnent';

    protected static ?string $pluralModelLabel = 'Abonnenten';

    protected static UnitEnum|string|null $navigationGroup = 'Newsletter';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::UserGroup;

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teilnehmer')
                    ->description('Wähle einen bestehenden Teilnehmer oder erstelle einen neuen')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Teilnehmer')
                            ->relationship('participant', 'email')
                            ->getOptionLabelFromRecordUsing(fn (Participant $record): string => $record->fullName
                                ? "{$record->fullName} ({$record->email})"
                                : $record->email)
                            ->required()
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('first_name')
                                    ->label('Vorname')
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Nachname')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-Mail-Adresse')
                                    ->email()
                                    ->required()
                                    ->unique()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Telefonnummer')
                                    ->tel()
                                    ->maxLength(30),
                            ])
                            ->native(false),
                    ]),

                Section::make('Abonnement-Status')
                    ->description('Zeitstempel des Abonnements')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('subscribed_at')
                            ->label('Angemeldet am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                        DateTimePicker::make('confirmed_at')
                            ->label('Bestätigt am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                        DateTimePicker::make('unsubscribed_at')
                            ->label('Abgemeldet am')
                            ->disabled()
                            ->dehydrated()
                            ->displayFormat('d.m.Y H:i')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant.first_name')
                    ->label('Vorname')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('participant.last_name')
                    ->label('Nachname')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('participant.email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->state(fn ($record): bool => $record->isActive()),
                TextColumn::make('subscribed_at')
                    ->label('Angemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('unsubscribed_at')
                    ->label('Abgemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Aktive Abonnenten')
                    ->query(fn (Builder $query): Builder => $query->whereNull('unsubscribed_at'))
                    ->default(),
                Filter::make('unsubscribed')
                    ->label('Abgemeldete')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('unsubscribed_at')),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->recordActions([
                EditAction::make(),
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
            'index' => Pages\ListNewsletterSubscriptions::route('/'),
            'create' => Pages\CreateNewsletterSubscription::route('/create'),
            'edit' => Pages\EditNewsletterSubscription::route('/{record}/edit'),
        ];
    }
}
