<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\ClearSettingsCache;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
use Exception;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);

        EventFacade::listen(SettingsSaved::class, ClearSettingsCache::class);

        Vite::useAggressivePrefetching();

        // Share data with all views using View::share() for better performance
        // This runs once per request instead of once per view like View::composer()
        $this->shareGlobalViewData();
    }

    /**
     * Share global data with all views.
     * Uses forever caching with event-based invalidation for optimal performance.
     */
    protected function shareGlobalViewData(): void
    {
        try {
            // Cache check for next event (invalidated via EventObserver)
            $hasNextEvent = cache()->rememberForever('has_next_event', function () {
                return Event::query()
                    ->where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists();
            });

            // Get settings (Spatie Settings handles caching internally - no double caching needed)
            $settings = app_settings();

            // Share all data at once using View::share() - faster than View::composer()
            View::share([
                'hasNextEvent' => $hasNextEvent,
                'settings' => $settings,
                'siteName' => $settings->site_name,
                'siteTagline' => $settings->site_tagline,
                'siteDescription' => $settings->site_description,
                'contactEmail' => $settings->contact_email,
                'contactPhone' => $settings->contact_phone,
                'socialLinks' => $settings->social_links ?? [],
                'footerText' => $settings->footer_text,
                'whatsappCommunityLink' => $settings->whatsapp_community_link,
            ]);
        } catch (Exception $exception) {
            // During build time or when database is unavailable, skip view data sharing
            // This prevents "could not find driver" errors during composer dump-autoload
        }
    }
}
