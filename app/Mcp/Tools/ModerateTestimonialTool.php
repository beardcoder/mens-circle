<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\ModerateAiTestimonial;
use App\Models\Testimonial;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

final class ModerateTestimonialTool extends Tool
{
    protected string $name = 'moderate_testimonial';

    protected string $description = 'Moderiert ein Testimonial. Erwartet testimonial_id, decision=publish|reject und confirm=true.';

    public function __construct(
        private readonly ModerateAiTestimonial $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->boolean('confirm')) {
            throw new RuntimeException('Zur Moderation ist confirm=true erforderlich.');
        }

        $testimonial = Testimonial::withTrashed()->findOrFail((int) $request->get('testimonial_id'));
        $decision = (string) $request->get('decision', 'publish');

        if ($decision === 'reject') {
            $this->action->reject($testimonial);

            return Response::structured([
                'message' => 'Das Testimonial wurde abgelehnt.',
            ]);
        }

        $testimonial = $this->action->publish($testimonial);

        return Response::structured([
            'data' => $this->formatter->testimonial($testimonial),
        ]);
    }
}
