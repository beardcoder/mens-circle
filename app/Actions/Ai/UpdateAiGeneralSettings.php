<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Services\Ai\AiAuditLogger;
use App\Settings\GeneralSettings;

final readonly class UpdateAiGeneralSettings
{
    public function __construct(
        private GeneralSettings $settings,
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @var list<string>
     */
    private const ALLOWED_FIELDS = [
        'site_name',
        'site_tagline',
        'site_description',
        'contact_email',
        'contact_phone',
        'location',
        'whatsapp_community_link',
        'social_links',
        'footer_text',
        'event_default_max_participants',
    ];

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): GeneralSettings
    {
        $filtered = array_intersect_key($data, array_flip(self::ALLOWED_FIELDS));

        foreach ($filtered as $key => $value) {
            match ($key) {
                'site_name' => $this->settings->site_name = is_string($value) ? $value : '',
                'site_tagline' => $this->settings->site_tagline = is_string($value) ? $value : '',
                'site_description' => $this->settings->site_description = is_string($value) ? $value : '',
                'contact_email' => $this->settings->contact_email = is_string($value) ? $value : '',
                'contact_phone' => $this->settings->contact_phone = is_string($value) ? $value : null,
                'location' => $this->settings->location = is_string($value) ? $value : '',
                'whatsapp_community_link' => $this->settings->whatsapp_community_link = is_string($value) ? $value : null,
                'social_links' => $this->settings->social_links = is_array($value) ? array_map(static fn(mixed $v): string => is_string($v) ? $v : '', $value) : null,
                'footer_text' => $this->settings->footer_text = is_string($value) ? $value : '',
                'event_default_max_participants' => $this->settings->event_default_max_participants = is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0),
                default => null,
            };
        }

        $this->settings->save();

        $this->auditLogger->log('ai.settings.updated', [
            'updated_fields' => array_keys($filtered),
        ]);

        return $this->settings;
    }
}
