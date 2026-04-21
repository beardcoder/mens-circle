<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Services\Ai\AiAuditLogger;
use RuntimeException;

final readonly class SendAiNewsletter
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    public function execute(Newsletter $newsletter): Newsletter
    {
        if ($newsletter->isSent()) {
            throw new RuntimeException('Dieser Newsletter wurde bereits versendet.');
        }

        dispatch(new SendNewsletterJob($newsletter));

        $this->auditLogger->log('ai.newsletter.send_requested', [
            'newsletter_id' => $newsletter->id,
        ]);

        return $newsletter;
    }
}
