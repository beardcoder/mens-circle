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
            $this->settings->{$key} = $value;
        }

        $this->settings->save();

        $this->auditLogger->log('ai.settings.updated', [
            'updated_fields' => array_keys($filtered),
        ]);

        return $this->settings;
    }
}
