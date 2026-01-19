<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Event;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Relationships\HasMany;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * MoonShine Resource for Event Model
 * This demonstrates MoonShine running parallel to Filament
 */
class EventResource extends ModelResource
{
    protected string $model = Event::class;

    protected string $title = 'Events';

    protected string $column = 'title';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * @var array<string>
     */
    protected array $with = ['registrations'];

    /**
     * Define the fields for the index (list) view.
     *
     * @return iterable<mixed>
     */
    protected function indexFields(): iterable
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

    /**
     * Define the fields for the detail/form views.
     *
     * @return iterable<mixed>
     */
    protected function formFields(): iterable
    {
        return [
            Grid::make([
                Column::make([
                    Box::make([
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
        return ['id', 'title', 'location', 'city'];
    }

    /**
     * Define filters for the index page.
     *
     * @return iterable<mixed>
     */
    protected function filters(): iterable
    {
        return [
            Switcher::make('Published Only', 'is_published'),
            Date::make('From Date', 'event_date')
                ->format('d.m.Y'),
        ];
    }

    /**
     * Define the rules for validation.
     *
     * @param Event $item
     * @return array<string, array<string>>
     */
    protected function rules(mixed $item): array
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
