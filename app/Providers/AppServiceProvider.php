<?php

declare(strict_types=1);

namespace App\Providers;

use App\Checks\MailHealthCheck;
use App\Checks\QueueHealthCheck;
use App\Checks\SevenIoHealthCheck;
use App\Models\Event;
use App\Models\Registration;
use App\Observers\RegistrationObserver;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Registration::observe(RegistrationObserver::class);

        $this->configureHealth();

        View::composer('*', static function (ViewContract $view): void {
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
        ], static function (ViewContract $view): void {
            try {
                $nextEvent = cache()->remember('next_event_data', 300, static fn() => Event::published()
                    ->upcoming()
                    ->orderBy('event_date')
                    ->first(['slug']));

                $view->with([
                    'hasNextEvent' => $nextEvent !== null,
                    'nextEventUrl' => $nextEvent ? route('event.show.slug', $nextEvent->slug) : route('event.show'),
                ]);
            } catch (Throwable) {
                $view->with([
                    'hasNextEvent' => false,
                    'nextEventUrl' => route('event.show'),
                ]);
            }
        });
    }

    private function configureHealth(): void
    {
        Health::checks([
            // Infrastructure Checks
            UsedDiskSpaceCheck::new()->warnWhenUsedSpaceIsAbovePercentage(70)->failWhenUsedSpaceIsAbovePercentage(90),
            DatabaseCheck::new(),
            CacheCheck::new(),

            // Schedule Check - wichtig für Event-Reminders und Sitemap
            ScheduleCheck::new()->useCacheStore(Config::string(
                'health.schedule.cache_store',
                'health',
            ))->heartbeatMaxAgeInMinutes(Config::integer('health.schedule.heartbeat_max_age_in_minutes', 10)),

            // Queue Check - wichtig für asynchrone Jobs
            QueueHealthCheck::new()->name('Queue System'),

            // Application Checks
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),

            // Website Availability Check
            PingCheck::new()
                ->url(config('app.url') ?? 'http://localhost') // @phpstan-ignore argument.type
                ->name('Website')
                ->timeout(5)
                ->retryTimes(2),

            // Mail System Check
            MailHealthCheck::new()->name('SMTP Mail'),

            // SMS Service Check (Seven.io)
            SevenIoHealthCheck::new()->name('Seven.io SMS'),
        ]);
    }
}
