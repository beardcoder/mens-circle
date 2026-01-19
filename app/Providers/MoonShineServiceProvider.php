<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Layouts\MoonShineLayout;
use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Resources\EventResource;
use App\MoonShine\Resources\RegistrationResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

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
            ->layout(MoonShineLayout::class)
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
    }
}
