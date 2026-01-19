<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Event;

use App\MoonShine\Resources\RegistrationResource;
use MoonShine\Laravel\Pages\Crud\FormPage;
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
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\UI\Fields\Textarea;

class EventFormPage extends FormPage
{
    /**
     * Define fields for the form (create/edit) view.
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

                        Text::make('Title', 'title')
                            ->required()
                            ->placeholder('Event title'),

                        Text::make('Slug', 'slug')
                            ->readonly(),

                        Date::make('Event Date', 'event_date')
                            ->required()
                            ->format('d.m.Y'),

                        Grid::make([
                            Column::make([
                                Date::make('Start Time', 'start_time')
                                    ->required()
                                    ->withTime()
                                    ->format('H:i'),
                            ])->columnSpan(6),

                            Column::make([
                                Date::make('End Time', 'end_time')
                                    ->required()
                                    ->withTime()
                                    ->format('H:i'),
                            ])->columnSpan(6),
                        ]),

                        Textarea::make('Description', 'description')
                            ->required()
                            ->placeholder('Event description'),

                        Image::make('Event Image', 'image')
                            ->disk('public')
                            ->dir('events')
                            ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp']),
                    ]),
                ])->columnSpan(8),

                Column::make([
                    Box::make([
                        Number::make('Max Participants', 'max_participants')
                            ->required()
                            ->min(1)
                            ->default(20),

                        Number::make('Cost Basis', 'cost_basis')
                            ->nullable()
                            ->step(0.01)
                            ->hint('Optional cost per participant'),

                        Switcher::make('Published', 'is_published')
                            ->default(false),
                    ]),

                    Box::make('Location', [
                        Text::make('Location Name', 'location')
                            ->placeholder('Venue name'),

                        Text::make('Street', 'street')
                            ->placeholder('Street address'),

                        Grid::make([
                            Column::make([
                                Text::make('Postal Code', 'postal_code')
                                    ->placeholder('PLZ'),
                            ])->columnSpan(4),

                            Column::make([
                                Text::make('City', 'city')
                                    ->placeholder('Stadt'),
                            ])->columnSpan(8),
                        ]),

                        Textarea::make('Location Details', 'location_details')
                            ->placeholder('Additional location information'),
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

    /**
     * Define validation rules for the form.
     *
     * @return array<string, array<string>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'is_published' => ['boolean'],
        ];
    }
}
