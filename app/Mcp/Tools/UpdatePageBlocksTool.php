<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\UpdateAiPageBlocks;
use App\Models\Page;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class UpdatePageBlocksTool extends Tool
{
    protected string $name = 'update_page_blocks';

    protected string $description = 'Ersetzt die Inhaltsblöcke einer Seite. Erwartet page_id und content_blocks.';

    public function __construct(
        private readonly UpdateAiPageBlocks $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        $page = Page::query()->with('contentBlocks')->findOrFail((int) $request->get('page_id'));
        $contentBlocks = $request->get('content_blocks', []);
        if (! is_array($contentBlocks)) {
            $contentBlocks = [];
        }

        $page = $this->action->execute($page, $contentBlocks);

        return Response::structured([
            'data' => $this->formatter->page($page),
        ]);
    }
}
