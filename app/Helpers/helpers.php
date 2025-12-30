<?php

declare(strict_types=1);

use App\Settings\GeneralSettings;

if (! function_exists('setting')) {
    /**
     * Get a single setting value by key.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return data_get(app_settings(), $key, $default);
        } catch (Exception) {
            return $default;
        }
    }
}

if (! function_exists('settings')) {
    /**
     * Get all settings as an array.
     */
    function settings(): array
    {
        try {
            $settings = app_settings();

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
        } catch (Exception) {
            return [];
        }
    }
}

if (! function_exists('app_settings')) {
    /**
     * Get the GeneralSettings instance.
     */
    function app_settings(): GeneralSettings
    {
        return resolve(GeneralSettings::class);
    }
}
