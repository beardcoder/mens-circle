<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Settings\GeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSettingsToSpatie extends Command
{
    protected $signature = 'settings:migrate-to-spatie';

    protected $description = 'Migrate settings from old Setting model to Spatie Settings';

    public function handle(): int
    {
        $this->info('Migrating settings from old system to Spatie Settings...');

        try {
            // Check if old settings table exists
            if (! DB::getSchemaBuilder()->hasTable('settings_old')) {
                $this->warn('Old settings table does not exist. Nothing to migrate.');

                return self::SUCCESS;
            }

            // Get settings instance
            $settings = resolve(GeneralSettings::class);

            // Get all old settings
            $oldSettings = DB::table('settings_old')->get()->pluck('value', 'key')->toArray();

            if (empty($oldSettings)) {
                $this->warn('No settings found in old table.');

                return self::SUCCESS;
            }

            // Migrate each setting (decode JSON values)
            $settings->site_name = $oldSettings['site_name'] ?? 'Männerkreis Niederbayern';
            $settings->site_tagline = $oldSettings['site_tagline'] ?? 'Ein Raum für echte Begegnung';
            $settings->site_description = $oldSettings['site_description'] ?? 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist.';
            $settings->contact_email = $oldSettings['contact_email'] ?? 'kontakt@mens-circle.de';
            $settings->contact_phone = $oldSettings['contact_phone'] ?? '';
            $settings->location = $oldSettings['location'] ?? 'Niederbayern';
            $settings->whatsapp_community_link = $oldSettings['whatsapp_community_link'] ?? '';

            // Decode social_links if it's a JSON string
            $socialLinks = $oldSettings['social_links'] ?? [];
            if (is_string($socialLinks)) {
                $socialLinks = json_decode($socialLinks, true) ?? [];
            }

            $settings->social_links = is_array($socialLinks) ? $socialLinks : [];

            $settings->footer_text = $oldSettings['footer_text'] ?? '© '.date('Y').' Männerkreis Niederbayern. Alle Rechte vorbehalten.';
            $settings->google_analytics_id = $oldSettings['google_analytics_id'] ?? '';
            $settings->event_default_max_participants = (int) ($oldSettings['event_default_max_participants'] ?? 8);

            // Save all settings
            $settings->save();

            $this->info('Migration completed successfully!');
            $this->info('Migrated '.count($oldSettings).' settings.');

            // Clear cache
            cache()->forget('settings_all');
            foreach (array_keys($oldSettings) as $key) {
                cache()->forget('setting.'.$key);
            }

            $this->info('Cache cleared.');

            return self::SUCCESS;
        } catch (\Exception $exception) {
            $this->error('Migration failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
