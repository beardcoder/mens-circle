<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    /** @var array<string, string> */
    private array $heroiconToType = [
        'envelope' => 'email',
        'at-symbol' => 'email',
        'phone' => 'phone',
        'globe-alt' => 'website',
        'link' => 'website',
        'chat-bubble-left-right' => 'whatsapp',
    ];

    public function up(): void
    {
        /** @var array<int, array<string, string>>|null $socialLinks */
        $socialLinks = $this->migrator->get('general.social_links', []);

        if (empty($socialLinks) || !is_array($socialLinks)) {
            return;
        }

        $updated = array_map(function (array $link): array {
            if (!isset($link['type']) && isset($link['icon']) && is_string($link['icon'])) {
                $link['type'] = $this->heroiconToType[$link['icon']] ?? 'other';
                unset($link['icon']);
            }
            return $link;
        }, $socialLinks);

        $this->migrator->update('general.social_links', $updated);
    }
};
