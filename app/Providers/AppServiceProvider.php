<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);

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
}
