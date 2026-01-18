<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Participants\Resources\Participants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantTable
{
    public static function configure(Table $table): Table
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
                    ->query(fn (Builder $query): Builder => $query->whereHas('newsletterSubscription', fn (Builder $q) => $q->whereNull('unsubscribed_at'))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
