<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
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

        return $nextEvent !== null && $nextEvent->active_registrations_count > 0;
    }

    public function table(Table $table): Table
    {
        $nextEvent = Event::nextEvent();

        if (! $nextEvent) {
            return $table
                ->query(Registration::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        return $table
            ->query(
                Registration::query()
                    ->with('participant')
                    ->where('event_id', $nextEvent->id)
                    ->whereIn('status', [RegistrationStatus::Registered->value, RegistrationStatus::Attended->value])
                    ->latest('registered_at')
            )
            ->columns([
                TextColumn::make('participant.first_name')
                    ->label('Vorname')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.last_name')
                    ->label('Nachname')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.email')
                    ->label('E-Mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('participant.phone')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (RegistrationStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (RegistrationStatus $state): string => $state->getLabel())
                    ->sortable(),

                TextColumn::make('registered_at')
                    ->label('Angemeldet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->heading('Anmeldungen für nächstes Event: ' . $nextEvent->title . ' (' . $nextEvent->event_date->format('d.m.Y') . ')')
            ->defaultSort('registered_at', 'desc');
    }
}
