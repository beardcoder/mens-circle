<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Page;
use App\Services\Ai\AiAuditLogger;

final readonly class SetAiPagePublicationState
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    public function execute(Page $page, bool $isPublished): Page
    {
        $page->update([
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);

        $this->auditLogger->log('ai.page.publication.updated', [
            'page_id' => $page->id,
            'is_published' => $isPublished,
        ]);

        return $page->fresh('contentBlocks') ?? $page;
    }
}
