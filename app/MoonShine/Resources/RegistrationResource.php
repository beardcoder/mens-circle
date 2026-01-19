<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Registration;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * MoonShine Resource for Registration Model
 */
class RegistrationResource extends ModelResource
{
    protected string $model = Registration::class;

    protected string $title = 'Registrations';

    protected string $column = 'id';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * @var array<string>
     */
    protected array $with = ['participant', 'event'];

    /**
     * Define the fields for the index (list) view.
     *
     * @return iterable<mixed>
     */
    protected function indexFields(): iterable
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

    /**
     * Define the fields for the detail/form views.
     *
     * @return iterable<mixed>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
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
     * Define the fields for the detail view.
     *
     * @return iterable<mixed>
     */
    protected function detailFields(): iterable
    {
        return $this->formFields();
    }

    /**
     * Define which fields are searchable.
     *
     * @return array<string>
     */
    public function search(): array
    {
        return ['id', 'status'];
    }

    /**
     * Define filters for the index page.
     *
     * @return iterable<mixed>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Status', 'status')
                ->options([
                    'registered' => 'Registered',
                    'attended' => 'Attended',
                    'cancelled' => 'Cancelled',
                    'no_show' => 'No Show',
                ]),
        ];
    }
}
