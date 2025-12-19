<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Page;
use App\Observers\EventObserver;
use App\Observers\PageObserver;
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
    }
}
