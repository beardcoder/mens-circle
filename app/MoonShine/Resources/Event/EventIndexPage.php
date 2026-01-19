<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Event;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

class EventIndexPage extends IndexPage
{
    /**
     * Define fields for the index (list) view.
     *
     * @return iterable<mixed>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),

            Text::make('Title', 'title')
                ->sortable(),

            Date::make('Event Date', 'event_date')
                ->format('d.m.Y')
                ->sortable(),

            Text::make('Location', 'location'),

            Number::make('Max Participants', 'max_participants')
                ->sortable(),

            Number::make('Active Registrations', 'active_registrations_count')
                ->sortable(),

            Switcher::make('Published', 'is_published')
                ->sortable(),
        ];
    }
}
