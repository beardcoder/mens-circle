<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Event;
use App\Models\Registration;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

class Dashboard extends Page
{
    protected string $title = 'Dashboard - MoonShine';

    /**
     * Page breadcrumbs.
     *
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    /**
     * Page components.
     *
     * @return iterable<mixed>
     */
    protected function components(): iterable
    {
        return [
            Heading::make('Welcome to MoonShine Admin'),

            Grid::make([
                Column::make([
                    Box::make([
                        ValueMetric::make('Total Events')
                            ->value(Event::count())
                            ->icon('heroicons.calendar'),
                    ]),
                ])->columnSpan(3),

                Column::make([
                    Box::make([
                        ValueMetric::make('Published Events')
                            ->value(Event::where('is_published', true)->count())
                            ->icon('heroicons.check-circle'),
                    ]),
                ])->columnSpan(3),

                Column::make([
                    Box::make([
                        ValueMetric::make('Upcoming Events')
                            ->value(Event::upcomingCount())
                            ->icon('heroicons.arrow-trending-up'),
                    ]),
                ])->columnSpan(3),

                Column::make([
                    Box::make([
                        ValueMetric::make('Total Registrations')
                            ->value(Registration::count())
                            ->icon('heroicons.users'),
                    ]),
                ])->columnSpan(3),
            ]),
        ];
    }
}
