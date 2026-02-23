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

    /** @var array<string, string> */
    private array $typeToHeroicon = [
        'email' => 'envelope',
        'phone' => 'phone',
        'website' => 'globe-alt',
        'whatsapp' => 'chat-bubble-left-right',
        'other' => 'link',
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

    public function down(): void
    {
        /** @var array<int, array<string, string>>|null $socialLinks */
        $socialLinks = $this->migrator->get('general.social_links', []);

        if (empty($socialLinks) || !is_array($socialLinks)) {
            return;
        }

        $reverted = array_map(function (array $link): array {
            if (!isset($link['icon']) && isset($link['type']) && is_string($link['type'])) {
                $link['icon'] = $this->typeToHeroicon[$link['type']] ?? 'link';
                unset($link['type']);
            }
            return $link;
        }, $socialLinks);

        $this->migrator->update('general.social_links', $reverted);
    }
};
