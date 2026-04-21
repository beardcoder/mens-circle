<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class GetPageTool extends Tool
{
    protected string $name = 'get_page';

    protected string $description = 'Gibt eine einzelne Seite mit Inhaltsblöcken zurück. Erwartet page_id.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        $page = Page::query()->with('contentBlocks')->findOrFail((int) $request->get('page_id'));

        return Response::structured([
            'data' => $this->formatter->page($page),
        ]);
    }
}
