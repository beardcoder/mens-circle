<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Page;
use App\Services\Ai\AiAuditLogger;

final readonly class UpdateAiPageBlocks
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<int, array<string, mixed>> $contentBlocks
     */
    public function execute(Page $page, array $contentBlocks): Page
    {
        $page->saveContentBlocks($contentBlocks);

        $this->auditLogger->log('ai.page.blocks.updated', [
            'page_id' => $page->id,
            'block_count' => count($contentBlocks),
        ]);

        return $page->fresh('contentBlocks');
    }
}
