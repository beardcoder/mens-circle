<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages\CreateParticipant;
use App\Filament\Resources\ParticipantResource\Pages\EditParticipant;
use App\Filament\Resources\ParticipantResource\Pages\ListParticipants;
use App\Models\NewsletterSubscription;
use App\Models\Participant;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Teilnehmer';

    protected static ?string $modelLabel = 'Teilnehmer';

    protected static ?string $pluralModelLabel = 'Teilnehmer';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Persönliche Daten')
                    ->columns(2)
                    ->schema([
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Telefonnummer')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpanFull(),
                    ]),
                Section::make('Newsletter')
                    ->schema([
                        Toggle::make('is_subscribed_to_newsletter')
                            ->label('Newsletter-Abonnement')
                            ->helperText('Aktivieren, um den Teilnehmer für den Newsletter anzumelden')
                            ->formatStateUsing(fn ($record) => $record?->isSubscribedToNewsletter() ?? false)
                            ->live()
                            ->afterStateUpdated(function ($state, $record): void {
                                if (!$record) {
                                    return;
                                }

                                $subscription = $record->newsletterSubscription;

                                if ($state) {
                                    self::handleNewsletterSubscribe($record, $subscription);

                                    return;
                                }

                                self::handleNewsletterUnsubscribe($subscription);
                            })
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Vorname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nachname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('registrations_count')
                    ->label('Anmeldungen')
                    ->counts('registrations')
                    ->sortable(),
                IconColumn::make('newsletter_subscription')
                    ->label('Newsletter')
                    ->boolean()
                    ->state(fn ($record): bool => $record->isSubscribedToNewsletter()),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_registrations')
                    ->label('Hat Anmeldungen')
                    ->query(fn (Builder $query): Builder => $query->has('registrations')),
                Filter::make('newsletter_subscriber')
                    ->label('Newsletter-Abonnent')
                    ->query(
                        fn (Builder $query): Builder => $query->whereHas('newsletterSubscription', fn (Builder $q) => $q->whereNull(
                            'unsubscribed_at'
                        ))
                    ),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make(), ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), ]), ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParticipants::route('/'),
            'create' => CreateParticipant::route('/create'),
            'edit' => EditParticipant::route('/{record}/edit'),
        ];
    }

    private static function handleNewsletterSubscribe(Participant $record, ?NewsletterSubscription $subscription): void
    {
        if ($subscription && !$subscription->isActive()) {
            $subscription->resubscribe();

            return;
        }

        if (!$subscription) {
            $record->newsletterSubscription()->create([]);
        }
    }

    private static function handleNewsletterUnsubscribe(?NewsletterSubscription $subscription): void
    {
        if ($subscription?->isActive()) {
            $subscription->unsubscribe();
        }
    }
}
