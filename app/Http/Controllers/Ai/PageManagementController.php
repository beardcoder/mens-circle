<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\GenerateAiPageDraft;
use App\Actions\Ai\SetAiPagePublicationState;
use App\Actions\Ai\UpdateAiPage;
use App\Actions\Ai\UpdateAiPageBlocks;
use App\Http\Requests\Ai\ConfirmPublicationRequest;
use App\Http\Requests\Ai\GeneratePageRequest;
use App\Http\Requests\Ai\UpdateAiPageRequest;
use App\Http\Requests\Ai\UpdatePageBlocksRequest;
use App\Models\Page;
use App\Services\Ai\AiDataFormatter;
use Illuminate\Http\JsonResponse;

final class PageManagementController
{
    public function index(AiDataFormatter $formatter): JsonResponse
    {
        $pages = Page::query()->with('contentBlocks')->orderBy('title')->get();

        return response()->json([
            'data' => $formatter->pages($pages),
        ]);
    }

    public function show(Page $page, AiDataFormatter $formatter): JsonResponse
    {
        $page->load('contentBlocks');

        return response()->json([
            'data' => $formatter->page($page),
        ]);
    }

    public function generate(GeneratePageRequest $request, GenerateAiPageDraft $action, AiDataFormatter $formatter): JsonResponse
    {
        $page = $action->execute($request->validated());

        return response()->json([
            'data' => $formatter->page($page),
        ], 201);
    }

    public function update(UpdateAiPageRequest $request, Page $page, UpdateAiPage $action, AiDataFormatter $formatter): JsonResponse
    {
        $page = $action->execute($page, $request->validated());

        return response()->json([
            'data' => $formatter->page($page),
        ]);
    }

    public function updateBlocks(UpdatePageBlocksRequest $request, Page $page, UpdateAiPageBlocks $action, AiDataFormatter $formatter): JsonResponse
    {
        /** @var array<int, array<string, mixed>> $contentBlocks */
        $contentBlocks = $request->validated()['content_blocks'];
        $page = $action->execute($page, $contentBlocks);

        return response()->json([
            'data' => $formatter->page($page),
        ]);
    }

    public function publish(ConfirmPublicationRequest $request, Page $page, SetAiPagePublicationState $action, AiDataFormatter $formatter): JsonResponse
    {
        $page = $action->execute($page, (bool) $request->validated()['is_published']);

        return response()->json([
            'data' => $formatter->page($page),
        ]);
    }
}
