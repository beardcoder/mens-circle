<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public string $site_tagline;

    public string $site_description;

    public string $contact_email;

    public string|null $contact_phone;

    public string $location;

    public string|null $whatsapp_community_link;

    public array|null $social_links;

    public string $footer_text;

    public int $event_default_max_participants;

    public static function group(): string
    {
        return 'general';
    }
}
