<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EventTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Von')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Bis')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Ort')
                    ->searchable(),
                TextColumn::make('confirmedRegistrations')
                    ->label('Anmeldungen')
                    ->counts('confirmedRegistrations')
                    ->formatStateUsing(fn ($record): string => $record->confirmedRegistrationsCount().' / '.$record->max_participants)
                    ->badge()
                    ->color(fn ($record): string => $record->isFull() ? 'danger' : 'success'),
                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
