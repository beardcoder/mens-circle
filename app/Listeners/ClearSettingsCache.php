<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelSettings\Events\SettingsSaved;

class ClearSettingsCache
{
    public function handle(SettingsSaved $event): void
    {
        Cache::forget('view_composer_settings');
    }
}
