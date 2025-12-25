<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Page;
use App\Observers\EventObserver;
use App\Observers\PageObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Vite to prefetch assets
        Vite::prefetch(concurrency: 3);

        Page::observe(PageObserver::class);
        Event::observe(EventObserver::class);

        View::composer('*', function ($view) {
            $hasNextEvent = cache()->remember('has_next_event', 600, function () {
                return Event::where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists();
            });

            $settings = settings();

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
