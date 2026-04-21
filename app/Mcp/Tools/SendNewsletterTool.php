<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\SendAiNewsletter;
use App\Models\Newsletter;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

final class SendNewsletterTool extends Tool
{
    protected string $name = 'send_newsletter';

    protected string $description = 'Fordert den Versand eines Newsletter-Entwurfs an. Erwartet newsletter_id und confirm_send=true.';

    public function __construct(
        private readonly SendAiNewsletter $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        if (! $request->boolean('confirm_send')) {
            throw new RuntimeException('Zum Versand ist confirm_send=true erforderlich.');
        }

        $newsletter = Newsletter::query()->findOrFail($request->integer('newsletter_id'));
        $newsletter = $this->action->execute($newsletter);

        return Response::structured([
            'data' => $this->formatter->newsletter($newsletter),
            'message' => 'Der Newsletter wird im Hintergrund versendet.',
        ]);
    }
}
