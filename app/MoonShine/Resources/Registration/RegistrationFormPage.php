<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Registration;

use App\MoonShine\Resources\EventResource;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\UI\Fields\Text;

class RegistrationFormPage extends FormPage
{
    /**
     * Define fields for the form (create/edit) view.
     *
     * @return iterable<mixed>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),

                BelongsTo::make('Event', 'event', resource: EventResource::class)
                    ->required(),

                Text::make('Participant', 'participant.full_name')
                    ->readonly(),

                Select::make('Status', 'status')
                    ->options([
                        'registered' => 'Registered',
                        'attended' => 'Attended',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ])
                    ->required(),

                Date::make('Registered At', 'registered_at')
                    ->format('d.m.Y H:i'),

                Date::make('Cancelled At', 'cancelled_at')
                    ->format('d.m.Y H:i')
                    ->nullable(),
            ]),
        ];
    }

    /**
     * Define validation rules.
     *
     * @return array<string, array<string>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'status' => ['required', 'string'],
        ];
    }
}
