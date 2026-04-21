<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Page;
use App\Services\Ai\AiAuditLogger;
use Carbon\CarbonImmutable;

final readonly class UpdateAiPage
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(Page $page, array $data): Page
    {
        if (isset($data['published_at']) && is_string($data['published_at'])) {
            $data['published_at'] = CarbonImmutable::parse($data['published_at']);
        }

        $page->update($data);

        $this->auditLogger->log('ai.page.updated', [
            'page_id' => $page->id,
            'updated_fields' => array_keys($data),
        ]);

        return $page->fresh('contentBlocks') ?? $page;
    }
}
