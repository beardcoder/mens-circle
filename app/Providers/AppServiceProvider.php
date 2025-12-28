<?php

namespace App\Providers;

use App\Models\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::useAggressivePrefetching();

        View::composer('*', function ($view): void {
            $hasNextEvent = Event::query()
                ->where('is_published', true)
                ->where('event_date', '>=', now())
                ->exists();

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
