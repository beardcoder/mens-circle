<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Registration;
use App\MoonShine\Resources\Registration\RegistrationDetailPage;
use App\MoonShine\Resources\Registration\RegistrationFormPage;
use App\MoonShine\Resources\Registration\RegistrationIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * MoonShine Resource for Registration Model
 */
class RegistrationResource extends ModelResource
{
    protected string $model = Registration::class;

    protected string $title = 'Registrations';

    protected string $column = 'id';

    protected string $sortColumn = 'registered_at';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * Eager load relationships to optimize queries.
     *
     * @var array<string>
     */
    protected array $with = ['participant', 'event'];

    /**
     * Define the pages for this resource.
     *
     * @return array<class-string>
     */
    protected function pages(): array
    {
        return [
            RegistrationIndexPage::class,
            RegistrationFormPage::class,
            RegistrationDetailPage::class,
        ];
    }

    /**
     * Define which fields are searchable.
     *
     * @return array<string>
     */
    protected function search(): array
    {
        return ['id', 'status'];
    }
}
