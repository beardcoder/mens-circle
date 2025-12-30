<?php

declare(strict_types=1);

namespace App\Listeners;

use Spatie\LaravelSettings\Events\SettingsSaved;

class ClearSettingsCache
{
    /**
     * Handle settings saved event.
     * Note: Spatie Laravel Settings handles its own cache invalidation automatically.
     * This listener is kept for potential future custom caching needs.
     */
    public function handle(SettingsSaved $event): void
    {
        // Spatie Settings automatically invalidates its cache when settings are saved
        // No manual cache clearing needed for the settings object itself
    }
}
