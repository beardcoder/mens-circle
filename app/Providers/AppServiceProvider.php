<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Seo\Schemas\LocalBusinessSchema;
use App\Seo\Schemas\OrganizationSchema;
use App\Seo\Schemas\WebSiteSchema;
use App\Settings\GeneralSettings;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::authorizationView(static fn(array $parameters) => view('mcp.authorize', $parameters));

        View::composer(['*'], static function (ViewContract $view): void {
            try {
                $settings = once(static fn(): GeneralSettings => app(GeneralSettings::class));

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

        View::composer(['layouts.*', 'emails.*', 'filament.*'], static function (ViewContract $view): void {
            try {
                $settings = once(static fn(): GeneralSettings => app(GeneralSettings::class));

                $view->with([
                    'localBusinessSchema' => new LocalBusinessSchema($settings),
                    'organizationSchema' => new OrganizationSchema($settings),
                    'websiteSchema' => new WebSiteSchema($settings),
                ]);
            } catch (Throwable) {
                $view->with([
                    'localBusinessSchema' => null,
                    'organizationSchema' => null,
                    'websiteSchema' => null,
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
                $nextEvent = Event::published()->upcoming()->orderBy('event_date')->first(['slug']);

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
}
