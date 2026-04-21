<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\GenerateAiPageDraft;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class GeneratePageContentTool extends Tool
{
    protected string $name = 'generate_page_content';

    protected string $description = 'Erstellt einen neuen Seiten-Entwurf mit Inhaltsblöcken. Erwartet title und prompt.';

    public function __construct(
        private readonly GenerateAiPageDraft $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $page = $this->action->execute($request->all());

        return Response::structured([
            'data' => $this->formatter->page($page),
        ]);
    }
}
