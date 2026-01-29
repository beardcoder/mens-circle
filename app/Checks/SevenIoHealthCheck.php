<?php

declare(strict_types=1);

namespace App\Checks;

use Illuminate\Support\Facades\Http;
use Override;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Throwable;

class SevenIoHealthCheck extends Check
{
    #[Override]
    public function run(): Result
    {
        $result = Result::make();
        $apiKey = config('sevenio.api_key', '');

        if (empty($apiKey)) {
            return $result
                ->warning('Seven.io API-Key nicht konfiguriert')
                ->shortSummary('Nicht konfiguriert');
        }

        try {
            $response = Http::timeout(10)
                ->withHeader('X-Api-Key', $apiKey)
                ->get('https://gateway.seven.io/api/balance');

            if ($response->successful()) {
                $balance = (float) $response->body();

                if ($balance < 1) {
                    return $result
                        ->warning(\sprintf('Niedriges Guthaben: %s€', $balance))
                        ->shortSummary($balance . '€')
                        ->meta(['balance' => $balance]);
                }

                return $result
                    ->ok()
                    ->shortSummary($balance . '€')
                    ->meta(['balance' => $balance]);
            }

            return $result
                ->failed(\sprintf('Seven.io API nicht erreichbar (HTTP %d)', $response->status()))
                ->shortSummary('API-Fehler');
        } catch (Throwable $throwable) {
            return $result
                ->failed('Seven.io Verbindungsfehler: ' . $throwable->getMessage())
                ->shortSummary('Verbindungsfehler');
        }
    }
}
