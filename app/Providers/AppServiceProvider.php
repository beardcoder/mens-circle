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

        View::composer('layouts.*', function (ViewContract $view): void {
            try {
                $view->with([
                    'hasNextEvent' => cache()->rememberForever(
                        'has_next_event',
                        fn (): bool => Event::query()
                            ->where('is_published', true)
                            ->where('event_date', '>=', now())
                            ->exists()
                    ),
                    'settings' => app(GeneralSettings::class),
                ]);
            } catch (Throwable) {
                $view->with([
                    'hasNextEvent' => false,
                    'settings' => null,
                ]);
            }
        });
    }
}
