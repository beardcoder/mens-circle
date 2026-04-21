<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\UpdateAiGeneralSettings;
use App\Http\Requests\Ai\UpdateGeneralSettingsRequest;
use App\Services\Ai\AiDataFormatter;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;

final class GeneralSettingsManagementController
{
    public function show(GeneralSettings $settings, AiDataFormatter $formatter): JsonResponse
    {
        return response()->json([
            'data' => $formatter->settings($settings),
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request, UpdateAiGeneralSettings $action, AiDataFormatter $formatter): JsonResponse
    {
        $settings = $action->execute($request->validated());

        return response()->json([
            'data' => $formatter->settings($settings),
        ]);
    }
}
