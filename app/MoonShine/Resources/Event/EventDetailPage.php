<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Event;

use App\MoonShine\Resources\RegistrationResource;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\HasMany;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

class EventDetailPage extends DetailPage
{
    /**
     * Define fields for the detail view.
     *
     * @return iterable<mixed>
     */
    protected function fields(): iterable
    {
        return [
            Grid::make([
                Column::make([
                    Box::make([
                        ID::make(),

                        Text::make('Title', 'title'),

                        Text::make('Slug', 'slug'),

                        Date::make('Event Date', 'event_date')
                            ->format('d.m.Y'),

                        Grid::make([
                            Column::make([
                                Date::make('Start Time', 'start_time')
                                    ->format('H:i'),
                            ])->columnSpan(6),

                            Column::make([
                                Date::make('End Time', 'end_time')
                                    ->format('H:i'),
                            ])->columnSpan(6),
                        ]),

                        Textarea::make('Description', 'description'),

                        Image::make('Event Image', 'image'),
                    ]),
                ])->columnSpan(8),

                Column::make([
                    Box::make([
                        Number::make('Max Participants', 'max_participants'),

                        Number::make('Active Registrations', 'active_registrations_count'),

                        Number::make('Cost Basis', 'cost_basis'),

                        Switcher::make('Published', 'is_published'),
                    ]),

                    Box::make('Location', [
                        Text::make('Location Name', 'location'),

                        Text::make('Street', 'street'),

                        Text::make('Postal Code', 'postal_code'),

                        Text::make('City', 'city'),

                        Textarea::make('Location Details', 'location_details'),
                    ]),
                ])->columnSpan(4),
            ]),

            HasMany::make('Registrations', 'registrations', resource: RegistrationResource::class)
                ->fields([
                    Text::make('Participant', 'participant.full_name'),
                    Text::make('Status', 'status'),
                    Date::make('Registered At', 'created_at')->format('d.m.Y H:i'),
                ]),
        ];
    }
}
