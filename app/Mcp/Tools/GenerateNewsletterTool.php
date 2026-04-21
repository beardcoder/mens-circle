<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\GenerateAiNewsletterDraft;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class GenerateNewsletterTool extends Tool
{
    protected string $name = 'generate_newsletter';

    protected string $description = 'Erstellt einen Newsletter-Entwurf auf Deutsch. Optional: prompt, subject, content.';

    public function __construct(
        private readonly GenerateAiNewsletterDraft $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $newsletter = $this->action->execute($request->all());

        return Response::structured([
            'data' => $this->formatter->newsletter($newsletter),
        ]);
    }
}
