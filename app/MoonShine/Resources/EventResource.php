<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Event;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\Date;
use MoonShine\Fields\Image;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\HasMany;
use MoonShine\Fields\Switcher;
use MoonShine\Fields\Text;
use MoonShine\Fields\Textarea;
use MoonShine\Fields\TinyMce;

/**
 * Example MoonShine Resource for Event Model
 * This demonstrates MoonShine running parallel to Filament
 */
class EventResource extends ModelResource
{
    protected string $model = Event::class;

    protected string $title = 'Events';

    protected string $column = 'title';

    protected array $with = ['registrations'];

    /**
     * Define the fields for the index (list) view
     */
    public function indexFields(): array
    {
        return [
            Text::make('Title', 'title')
                ->sortable()
                ->showOnExport(),

            Date::make('Event Date', 'event_date')
                ->format('d.m.Y')
                ->sortable(),

            Text::make('Location', 'location')
                ->showOnExport(),

            Number::make('Max Participants', 'max_participants')
                ->sortable(),

            Number::make('Active Registrations', 'active_registrations_count')
                ->sortable(),

            Switcher::make('Published', 'is_published')
                ->sortable()
                ->updateOnPreview(),
        ];
    }

    /**
     * Define the fields for the detail/form views
     */
    public function formFields(): array
    {
        return [
            Grid::make([
                Column::make([
                    Block::make([
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
                                    ->format('H:i'),
                            ])->columnSpan(6),

                            Column::make([
                                Date::make('End Time', 'end_time')
                                    ->required()
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
                    Block::make([
                        Number::make('Max Participants', 'max_participants')
                            ->required()
                            ->min(1)
                            ->default(20),

                        Number::make('Cost Basis', 'cost_basis')
                            ->nullable()
                            ->step('0.01')
                            ->hint('Optional cost per participant'),

                        Switcher::make('Published', 'is_published')
                            ->default(false),
                    ]),

                    Block::make('Location', [
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

            HasMany::make('Registrations', 'registrations')
                ->fields([
                    Text::make('Participant', 'participant.full_name'),
                    Text::make('Status', 'status'),
                    Date::make('Registered At', 'created_at')->format('d.m.Y H:i'),
                ]),
        ];
    }

    /**
     * Define the fields for the detail view
     */
    public function detailFields(): array
    {
        return $this->formFields();
    }

    /**
     * Define which fields are searchable
     */
    public function search(): array
    {
        return ['id', 'title', 'location', 'city'];
    }

    /**
     * Define filters for the index page
     */
    public function filters(): array
    {
        return [
            Switcher::make('Published Only', 'is_published'),
            Date::make('From Date', 'event_date')
                ->format('d.m.Y'),
        ];
    }

    /**
     * Actions available for bulk operations
     */
    public function actions(): array
    {
        return [
            // Bulk actions can be added here
        ];
    }

    /**
     * Define the rules for validation
     */
    public function rules($item): array
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
