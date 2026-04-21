<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Models\Testimonial;
use App\Services\Ai\AiAuditLogger;

final readonly class ModerateAiTestimonial
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    public function publish(Testimonial $testimonial): Testimonial
    {
        $testimonial->restore();
        $testimonial->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->auditLogger->log('ai.testimonial.published', [
            'testimonial_id' => $testimonial->id,
        ]);

        return $testimonial->fresh();
    }

    public function reject(Testimonial $testimonial): void
    {
        $testimonial->delete();

        $this->auditLogger->log('ai.testimonial.rejected', [
            'testimonial_id' => $testimonial->id,
        ]);
    }
}
