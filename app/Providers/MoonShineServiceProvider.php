<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\EventResource;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;

class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * Register MoonShine resources and configuration
     *
     * @return array<int, class-string>
     */
    protected function resources(): array
    {
        return [
            EventResource::class,
        ];
    }

    /**
     * Define MoonShine menu structure
     *
     * @return array<int, MenuItem|MenuGroup>
     */
    protected function menu(): array
    {
        return [
            MenuItem::make('Dashboard', 'moonshine.index')
                ->icon('heroicons.home'),

            MenuGroup::make('Content Management', [
                MenuItem::make('Events', EventResource::class)
                    ->icon('heroicons.calendar'),
            ]),

            MenuGroup::make('System', [
                MenuItem::make('Users', MoonShineUserResource::class)
                    ->icon('heroicons.users'),
                MenuItem::make('Roles', MoonShineUserRoleResource::class)
                    ->icon('heroicons.shield-check'),
            ]),
        ];
    }

    /**
     * Configure MoonShine theme colors
     *
     * @return array<string, array<string, string>>
     */
    protected function theme(): array
    {
        return [
            'colors' => [
                'primary' => '#1e40af',
                'secondary' => '#64748b',
            ],
        ];
    }
}
