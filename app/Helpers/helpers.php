<?php

use App\Settings\GeneralSettings;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return cache()->flexible('setting.'.$key, [3600, 7200], function () use ($key, $default) {
            try {
                $settings = resolve(GeneralSettings::class);

                return data_get($settings, $key, $default);
            } catch (\Exception $exception) {
                return $default;
            }
        });
    }
}

if (! function_exists('settings')) {
    function settings(): array
    {
        return cache()->flexible('settings_all', [3600, 7200], function (): array {
            try {
                $settings = resolve(GeneralSettings::class);

                return [
                    'site_name' => $settings->site_name,
                    'site_tagline' => $settings->site_tagline,
                    'site_description' => $settings->site_description,
                    'contact_email' => $settings->contact_email,
                    'contact_phone' => $settings->contact_phone,
                    'location' => $settings->location,
                    'whatsapp_community_link' => $settings->whatsapp_community_link,
                    'social_links' => $settings->social_links,
                    'footer_text' => $settings->footer_text,
                    'event_default_max_participants' => $settings->event_default_max_participants,
                ];
            } catch (\Exception $exception) {
                return [];
            }
        });
    }
}

if (! function_exists('app_settings')) {
    function app_settings(): GeneralSettings
    {
        return resolve(GeneralSettings::class);
    }
}
