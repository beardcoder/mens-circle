<?php

namespace App\Providers;

use App\Listeners\ClearSettingsCache;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
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

        View::composer('*', function ($view): void {
            $hasNextEvent = cache()->rememberForever('has_next_event', function () {
                return Event::query()
                    ->where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists();
            });

            $settings = cache()->rememberForever('view_composer_settings', function () {
                return settings();
            });

            $view->with([
                'hasNextEvent' => $hasNextEvent,
                'settings' => $settings,
                'siteName' => $settings['site_name'] ?? 'Männerkreis Niederbayern',
                'siteTagline' => $settings['site_tagline'] ?? '',
                'siteDescription' => $settings['site_description'] ?? '',
                'contactEmail' => $settings['contact_email'] ?? '',
                'contactPhone' => $settings['contact_phone'] ?? '',
                'socialLinks' => $settings['social_links'] ?? [],
                'footerText' => $settings['footer_text'] ?? '© '.date('Y').' Männerkreis Niederbayern',
                'whatsappCommunityLink' => $settings['whatsapp_community_link'] ?? '',
            ]);
        });
    }
}
