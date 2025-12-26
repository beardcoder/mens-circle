<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return cache()->remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }
}

if (! function_exists('settings')) {
    function settings(): array
    {
        return cache()->remember('settings_all', 3600, function () {
            $settings = Setting::all();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }

            return $result;
        });
    }
}
