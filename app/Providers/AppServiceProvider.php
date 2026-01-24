<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);

        $this->configureHealth();

        View::composer('*', function (ViewContract $view): void {
            try {
                $settings = app(GeneralSettings::class);

                $view->with([
                    'settings' => $settings,
                    'socialLinks' => $settings->social_links ?? [],
                ]);
            } catch (Throwable) {
                $view->with([
                    'settings' => null,
                    'socialLinks' => [],
                ]);
            }
        });

        View::composer([
            'errors.404',
            'layouts.app',
            'components.blocks.cta',
            'components.blocks.hero',
        ], function (ViewContract $view): void {
            try {
                $view->with([
                    'hasNextEvent' => cache()->remember('has_next_event', 300, fn () => Event::published()
                        ->upcoming()
                        ->exists()),
                ]);
            } catch (Throwable) {
                $view->with([
                    'hasNextEvent' => false,
                ]);
            }
        });
    }

    private function configureHealth(): void
    {
        Health::checks([
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
            DatabaseCheck::new(),
            CacheCheck::new(),
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);
    }
}
