<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Page;
use App\Models\Setting;
use App\Observers\EventObserver;
use App\Observers\PageObserver;
use App\Observers\SettingObserver;
use Illuminate\Support\Facades\View;
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
        Page::observe(PageObserver::class);
        Event::observe(EventObserver::class);
        Setting::observe(SettingObserver::class);

        View::composer('*', function ($view) {
            $hasNextEvent = cache()->remember('has_next_event', 600, function () {
                return Event::where('is_published', true)
                    ->where('event_date', '>=', now())
                    ->exists();
            });

            $socialLinks = cache()->remember('social_links', 3600, function () {
                return [
                    'website_url' => Setting::get('website_url', config('app.url')),
                    'whatsapp_url' => Setting::get('whatsapp_url'),
                    'github_url' => Setting::get('github_url'),
                    'contact_email' => Setting::get('contact_email', 'hallo@mens-circle.de'),
                ];
            });

            $view->with('hasNextEvent', $hasNextEvent);
            $view->with('socialLinks', $socialLinks);
        });
    }
}
