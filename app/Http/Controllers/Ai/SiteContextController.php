<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\BuildAiSiteContext;
use Illuminate\Http\JsonResponse;

final class SiteContextController
{
    public function __invoke(BuildAiSiteContext $action): JsonResponse
    {
        return response()->json($action->execute());
    }
}
