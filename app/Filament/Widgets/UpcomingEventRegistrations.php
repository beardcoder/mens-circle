<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\EventRegistrationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingEventRegistrations extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $nextEvent = Event::nextEvent();

        return $nextEvent !== null && $nextEvent->confirmed_registrations_count > 0;
    }

    public function table(Table $table): Table
    {
        $nextEvent = Event::nextEvent();

        if (! $nextEvent) {
            return $table
                ->query(EventRegistration::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        return $table
            ->query(
                EventRegistration::query()
                    ->where('event_id', $nextEvent->id)
                    ->where('status', 'confirmed')
                    ->latest()
            )
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
            ])
            ->heading('Anmeldungen für nächstes Event: '.$nextEvent->title.' ('.$nextEvent->event_date->format('d.m.Y').')')
            ->defaultSort('created_at', 'desc');
    }
}
