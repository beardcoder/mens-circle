<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Registration;

use App\MoonShine\Resources\EventResource;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\BelongsTo;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

class RegistrationIndexPage extends IndexPage
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

            BelongsTo::make('Event', 'event', resource: EventResource::class)
                ->sortable(),

            Text::make('Participant', 'participant.full_name'),

            Select::make('Status', 'status')
                ->options([
                    'registered' => 'Registered',
                    'attended' => 'Attended',
                    'cancelled' => 'Cancelled',
                    'no_show' => 'No Show',
                ])
                ->sortable(),

            Date::make('Registered At', 'registered_at')
                ->format('d.m.Y H:i')
                ->sortable(),
        ];
    }
}
