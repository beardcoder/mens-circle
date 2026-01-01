<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\ResultStores\ResultStore;

class HealthController extends Controller
{
    public function show(): View
    {
        $checkResults = app(ResultStore::class)->latestResults();

        return view('health', [
            'lastRanAt' => new \DateTimeImmutable($checkResults?->finishedAt?->toDateTimeString() ?? 'now'),
            'checkResults' => $checkResults,
        ]);
    }

    public function json(): JsonResponse
    {
        $checkResults = app(ResultStore::class)->latestResults();

        return response()->json([
            'finishedAt' => $checkResults?->finishedAt,
            'checkResults' => $checkResults?->storedCheckResults->map(function ($result) {
                return [
                    'name' => $result->name,
                    'label' => $result->label,
                    'notificationMessage' => $result->notificationMessage,
                    'shortSummary' => $result->shortSummary,
                    'status' => $result->status,
                ];
            }),
        ]);
    }

    public function run(): JsonResponse
    {
        \Artisan::call(RunHealthChecksCommand::class);

        return $this->json();
    }
}
