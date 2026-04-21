<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Page;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class ListPagesTool extends Tool
{
    protected string $name = 'list_pages';

    protected string $description = 'Listet Seiten inklusive Inhaltsblöcken auf. Optional: published=true.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $query = Page::query()->with('contentBlocks')->orderBy('title');

        if ($request->boolean('published', false)) {
            $query->published();
        }

        return Response::structured([
            'data' => $this->formatter->pages($query->get()),
        ]);
    }
}
