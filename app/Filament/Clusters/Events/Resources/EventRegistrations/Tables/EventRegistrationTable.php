<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Events\Resources\EventRegistrations\Tables;

use App\Enums\EventRegistrationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EventRegistrationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.event_date')
                    ->label('Event-Datum')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Veranstaltung')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('phone_number')
                    ->label('Handynummer')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => EventRegistrationStatus::tryFrom($state)?->getColor() ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => EventRegistrationStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Angemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Gelöscht am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Gelöschte Anmeldungen'),
                SelectFilter::make('status')
                    ->label('Anmeldestatus')
                    ->options(EventRegistrationStatus::options()),
                SelectFilter::make('event')
                    ->label('Veranstaltung')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event.event_date', 'desc');
    }
}
