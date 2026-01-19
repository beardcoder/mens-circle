<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Registration;

use App\MoonShine\Resources\EventResource;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

class RegistrationDetailPage extends DetailPage
{
    /**
     * Define fields for the detail view.
     *
     * @return iterable<mixed>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),

                BelongsTo::make('Event', 'event', resource: EventResource::class),

                Text::make('Participant', 'participant.full_name'),

                Select::make('Status', 'status')
                    ->options([
                        'registered' => 'Registered',
                        'attended' => 'Attended',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ]),

                Date::make('Registered At', 'registered_at')
                    ->format('d.m.Y H:i'),

                Date::make('Cancelled At', 'cancelled_at')
                    ->format('d.m.Y H:i'),
            ]),
        ];
    }
}
