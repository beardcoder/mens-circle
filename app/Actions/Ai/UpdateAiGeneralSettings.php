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
     * @param array<string, mixed> $data
     */
    public function execute(array $data): GeneralSettings
    {
        foreach ($data as $key => $value) {
            $this->settings->{$key} = $value;
        }

        $this->settings->save();

        $this->auditLogger->log('ai.settings.updated', [
            'updated_fields' => array_keys($data),
        ]);

        return $this->settings;
    }
}
