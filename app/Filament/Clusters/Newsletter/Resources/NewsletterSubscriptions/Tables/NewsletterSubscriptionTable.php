<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter\Resources\NewsletterSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscriptionTable
{
    public static function configure(Table $table): Table
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
}
