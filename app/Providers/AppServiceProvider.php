<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);

        $this->shareGlobalViewData();
    }

    protected function shareGlobalViewData(): void
    {
        View::share([
            'hasNextEvent' => cache()->rememberForever(
                'has_next_event',
                fn (): bool => Event::query()
                    ->where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists()
            ),
            'settings' => app(GeneralSettings::class),
        ]);
    }
}
