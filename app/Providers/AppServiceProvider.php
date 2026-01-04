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
    private ?bool $hasNextEvent = null;

    private ?GeneralSettings $settings = null;

    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);

        View::composer('*', function (ViewContract $view): void {
            try {
                $this->hasNextEvent ??= cache()->rememberForever(
                    'has_next_event',
                    fn (): bool => Event::query()
                        ->where('is_published', true)
                        ->where('event_date', '>=', now())
                        ->exists()
                );
                $this->settings ??= app(GeneralSettings::class);

                $view->with([
                    'hasNextEvent' => $this->hasNextEvent,
                    'settings' => $this->settings,
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
