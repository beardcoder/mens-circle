<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PushoverService
{
    private const string API_URL = 'https://api.pushover.net/1/messages.json';

    /**
     * @param array{
     *     title?: string,
     *     url?: string,
     *     url_title?: string,
     *     priority?: int,
     *     sound?: string,
     *     html?: int,
     * } $options
     */
    public function send(string $message, array $options = []): bool
    {
        /** @var string|null $token */
        $token = config('pushover.token');
        /** @var string|null $userKey */
        $userKey = config('pushover.user_key');

        if (!$token || !$userKey) {
            Log::warning('Cannot send Pushover notification - token or user key not configured');

            return false;
        }

        try {
            /** @var Response $response */
            $response = Http::post(self::API_URL, [
                'token' => $token,
                'user' => $userKey,
                'message' => $message,
                ...$options,
            ]);

            if (!$response->successful()) {
                Log::error('Pushover API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (ConnectionException|Exception $exception) {
            Log::error('Failed to send Pushover notification', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
