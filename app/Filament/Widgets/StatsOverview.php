<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\NewsletterSubscription;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $nextEvent = Event::nextEvent();

        return [
            Stat::make('Kommende Events', Event::upcomingCount())
                ->description('Veröffentlichte Events')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Anmeldungen', Registration::registeredCount())
                ->description('Aktive Anmeldungen')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Newsletter-Abonnenten', NewsletterSubscription::activeCount())
                ->description('Aktive Abonnenten')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('warning'),

            Stat::make('Verfügbare Plätze', $nextEvent instanceof Event ? $nextEvent->availableSpots : 0)
                ->description($this->getNextEventDescription($nextEvent))
                ->descriptionIcon('heroicon-o-ticket')
                ->color(fn (): string => $this->getAvailableSpotsColor()),
        ];
    }

    private function getNextEventDescription(?Event $event): string
    {
        return $event instanceof Event
            ? "Nächstes Event: {$event->event_date->format('d.m.Y')}"
            : 'Kein Event geplant';
    }

    private function getAvailableSpotsColor(): string
    {
        return 'danger';
    }
}
