<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Männerkreis Niederbayern');
        $this->migrator->add('general.site_tagline', 'Ein Raum für echte Begegnung');
        $this->migrator->add('general.site_description', 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist.');
        $this->migrator->add('general.contact_email', 'kontakt@mens-circle.de');
        $this->migrator->add('general.contact_phone', '');
        $this->migrator->add('general.location', 'Niederbayern');
        $this->migrator->add('general.whatsapp_community_link', '');
        $this->migrator->add('general.social_links', []);
        $this->migrator->add('general.footer_text', '© '.date('Y').' Männerkreis Niederbayern. Alle Rechte vorbehalten.');
        $this->migrator->add('general.event_default_max_participants', 8);
    }
};
