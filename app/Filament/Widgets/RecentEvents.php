<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentEvents extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Event::query() ->where('event_date', '>=', now()) ->oldest('event_date') ->limit(5))
            ->columns([
                TextColumn::make('title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('active_registrations_count')
                    ->label('Anmeldungen')
                    ->counts('activeRegistrations')
                    ->badge()
                    ->color('success'),

                TextColumn::make('available_spots')
                    ->label('Freie Plätze')
                    ->getStateUsing(fn(Event $record): int => $record->availableSpots)
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 3 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Veröffentlicht' : 'Entwurf')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->heading('Kommende Events')
            ->defaultSort('event_date', 'asc');
    }
}
