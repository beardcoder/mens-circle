<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\SetAiPagePublicationState;
use App\Models\Page;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

final class PublishPageTool extends Tool
{
    protected string $name = 'publish_page';

    protected string $description = 'Veröffentlicht oder versteckt eine Seite. Erwartet page_id, is_published und confirm=true.';

    public function __construct(
        private readonly SetAiPagePublicationState $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        if (! $request->boolean('confirm')) {
            throw new RuntimeException('Zum Veröffentlichen ist confirm=true erforderlich.');
        }

        $page = Page::query()->with('contentBlocks')->findOrFail($request->integer('page_id'));
        $page = $this->action->execute($page, $request->boolean('is_published', true));

        return Response::structured([
            'data' => $this->formatter->page($page),
        ]);
    }
}
