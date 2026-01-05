<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Models\Page;
use App\Observers\EventObserver;
use App\Observers\PageObserver;
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
        Page::observe(PageObserver::class);

        View::composer('*', function (ViewContract $view): void {
            try {
                $hasNextEvent = Event::query()
                    ->where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists();

                $settings = app(GeneralSettings::class);

                $view->with([
                    'hasNextEvent' => $hasNextEvent,
                    'settings' => $settings,
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
