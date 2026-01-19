<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Event;
use App\Models\Registration;
use MoonShine\Pages\Page;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Metrics\ValueMetric;

class Dashboard extends Page
{
    /**
     * Page title
     */
    public function title(): string
    {
        return 'Dashboard - MoonShine';
    }

    /**
     * Page breadcrumbs
     */
    public function breadcrumbs(): array
    {
        return [
            '#' => $this->title(),
        ];
    }

    /**
     * Page components
     */
    public function components(): array
    {
        return [
            Grid::make([
                Column::make([
                    Block::make('Welcome to MoonShine', [
                        // Welcome content
                    ]),
                ])->columnSpan(12),

                Column::make([
                    ValueMetric::make('Total Events')
                        ->value(Event::count())
                        ->icon('heroicons.calendar'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Published Events')
                        ->value(Event::where('is_published', true)->count())
                        ->icon('heroicons.check-circle'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Upcoming Events')
                        ->value(Event::upcomingCount())
                        ->icon('heroicons.arrow-trending-up'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Total Registrations')
                        ->value(Registration::count())
                        ->icon('heroicons.users'),
                ])->columnSpan(3),
            ]),
        ];
    }
}
