<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Resources\EventResource;
use App\MoonShine\Resources\RegistrationResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param CoreContract&MoonShine $core
     * @param MoonShineConfigurator $config
     */
    public function boot(
        CoreContract $core,
        MoonShineConfigurator $config,
    ): void {
        $config
            ->title('Männerkreis Niederbayern - MoonShine')
            ->logo('/logo-color.svg')
            ->useMigrations()
            ->useNotifications()
            ->authorizationRules(static fn (): bool => true);

        $core
            ->resources([
                EventResource::class,
                RegistrationResource::class,
            ])
            ->pages([
                Dashboard::class,
            ]);

        $this->buildMenu($core);
    }

    /**
     * Build the MoonShine menu structure.
     *
     * @param CoreContract&MoonShine $core
     */
    private function buildMenu(CoreContract $core): void
    {
        $core->menu([
            MenuItem::make('Dashboard', Dashboard::class)
                ->icon('heroicons.home'),

            MenuGroup::make('Content Management', [
                MenuItem::make('Events', EventResource::class)
                    ->icon('heroicons.calendar'),
                MenuItem::make('Registrations', RegistrationResource::class)
                    ->icon('heroicons.user-group'),
            ]),
        ]);
    }
}
