<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $upcomingEvents = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->count();

        $totalRegistrations = EventRegistration::where('status', 'confirmed')
            ->count();

        $activeSubscribers = NewsletterSubscription::where('status', 'active')
            ->count();

        $nextEvent = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->withCount('confirmedRegistrations')
            ->orderBy('event_date')
            ->first();

        $nextEventSpots = $nextEvent ? $nextEvent->availableSpots() : 0;

        return [
            Stat::make('Kommende Events', $upcomingEvents)
                ->description('Veröffentlichte Events')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Anmeldungen', $totalRegistrations)
                ->description('Bestätigte Teilnehmer')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Newsletter-Abonnenten', $activeSubscribers)
                ->description('Aktive Abonnenten')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('warning'),

            Stat::make('Verfügbare Plätze', $nextEventSpots)
                ->description($nextEvent ? 'Nächstes Event: '.$nextEvent->event_date->format('d.m.Y') : 'Kein Event geplant')
                ->descriptionIcon('heroicon-o-ticket')
                ->color($nextEventSpots > 3 ? 'success' : ($nextEventSpots > 0 ? 'warning' : 'danger')),
        ];
    }
}
