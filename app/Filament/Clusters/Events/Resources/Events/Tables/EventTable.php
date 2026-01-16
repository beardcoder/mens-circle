<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('confirmedRegistrations'))
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): ?string => $record->description ? str($record->description)->limit(50)->toString() : null)
                    ->wrap(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->description(fn ($record): string => $record->start_time->format('H:i').' - '.$record->end_time->format('H:i'))
                    ->color(fn ($record): string => $record->isPast ? 'gray' : ($record->event_date->isToday() ? 'warning' : 'primary')),
                TextColumn::make('location')
                    ->label('Ort')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('confirmed_registrations_count')
                    ->label('Anmeldungen')
                    ->formatStateUsing(fn ($record): string => $record->confirmed_registrations_count.' / '.$record->max_participants)
                    ->badge()
                    ->color(fn ($record): string => $record->isFull ? 'danger' : ($record->confirmed_registrations_count > ($record->max_participants * 0.8) ? 'warning' : 'success'))
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Veröffentlicht')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_published')
                    ->label('Veröffentlichungsstatus')
                    ->options([
                        '1' => 'Veröffentlicht',
                        '0' => 'Entwurf',
                    ]),
                Filter::make('upcoming')
                    ->label('Kommende Events')
                    ->query(fn (Builder $query): Builder => $query->where('event_date', '>=', now()))
                    ->toggle()
                    ->default(),
                Filter::make('past')
                    ->label('Vergangene Events')
                    ->query(fn (Builder $query): Builder => $query->where('event_date', '<', now()))
                    ->toggle(),
                Filter::make('full')
                    ->label('Ausgebuchte Events')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_id = events.id AND status = ?) >= max_participants', ['confirmed']))
                    ->toggle(),
                TrashedFilter::make()
                    ->label('Gelöschte Events'),
            ])
            ->deferFilters(false)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_date', 'desc')
            ->persistFiltersInSession()
            ->striped();
    }
}
