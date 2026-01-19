<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Event;
use App\MoonShine\Resources\Event\EventDetailPage;
use App\MoonShine\Resources\Event\EventFormPage;
use App\MoonShine\Resources\Event\EventIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * MoonShine Resource for Event Model
 * This demonstrates MoonShine v4 running parallel to Filament
 *
 * @extends ModelResource<Event, EventIndexPage, EventFormPage, EventDetailPage>
 */
class EventResource extends ModelResource
{
    protected string $model = Event::class;

    protected string $title = 'Events';

    protected string $column = 'title';

    protected string $sortColumn = 'event_date';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * Eager load relationships to optimize queries.
     *
     * @var array<string>
     */
    protected array $with = ['registrations'];

    /**
     * Define the pages for this resource.
     *
     * @return array<class-string>
     */
    protected function pages(): array
    {
        return [
            EventIndexPage::class,
            EventFormPage::class,
            EventDetailPage::class,
        ];
    }

    /**
     * Define which fields are searchable.
     *
     * @return array<string>
     */
    protected function search(): array
    {
        return ['id', 'title', 'location', 'city'];
    }
}
