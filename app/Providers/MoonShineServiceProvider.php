<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\EventResource;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\MoonShine;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;

class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * Register MoonShine resources and configuration
     */
    protected function resources(): array
    {
        return [
            // Example resource - Event model
            EventResource::class,
        ];
    }

    /**
     * Define MoonShine menu structure
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
     * Additional MoonShine configuration
     */
    protected function theme(): array
    {
        return [
            'colors' => [
                'primary' => '#1e40af', // Blue-700
                'secondary' => '#64748b', // Slate-500
            ],
        ];
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        parent::boot();

        // Additional MoonShine customization can go here
    }

    /**
     * Register any application services
     */
    public function register(): void
    {
        parent::register();

        // Register additional services if needed
    }
}
