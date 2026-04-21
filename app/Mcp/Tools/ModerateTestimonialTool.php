<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\ModerateAiTestimonial;
use App\Models\Testimonial;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
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

    public function handle(Request $request): ResponseFactory
    {
        if (! $request->boolean('confirm')) {
            throw new RuntimeException('Zur Moderation ist confirm=true erforderlich.');
        }

        $testimonial = Testimonial::withTrashed()->findOrFail($request->integer('testimonial_id'));
        $decision = $request->string('decision', 'publish')->toString();

        if (! in_array($decision, ['publish', 'reject'], true)) {
            throw new RuntimeException('decision muss publish oder reject sein.');
        }

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
