<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Resources\EventResource;
use App\MoonShine\Resources\RegistrationResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

class MoonShineLayout extends AppLayout
{
    /**
     * Build the menu structure for the admin panel.
     *
     * @return array<MenuItem|MenuGroup>
     */
    protected function menu(): array
    {
        return [
            MenuItem::make(Dashboard::class)
                ->icon('home'),

            MenuGroup::make('Content Management', [
                MenuItem::make(EventResource::class)
                    ->icon('calendar'),
                MenuItem::make(RegistrationResource::class)
                    ->icon('user-group'),
            ])->icon('document-text'),
        ];
    }
}
