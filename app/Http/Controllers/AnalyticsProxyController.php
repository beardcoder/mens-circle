<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AnalyticsProxyController
{
    public function script(): Response
    {
        $scriptUrl = config('analytics.umami.script_url');

        $script = Cache::remember('umami_script', 86400, fn (): string => Http::get($scriptUrl)->body());

        return response($script, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function collect(Request $request): Response
    {
        $scriptUrl = config('analytics.umami.script_url');
        $baseUrl = preg_replace('#/[^/]+$#', '', $scriptUrl);

        $data = $request->all();

        if (isset($data['payload']) && is_array($data['payload'])) {
            $data['payload']['referrer'] = $data['payload']['referrer'] ?? '';
            $data['payload']['language'] = $data['payload']['language'] ?? '';
            $data['payload']['screen'] = $data['payload']['screen'] ?? '';
            $data['payload']['title'] = $data['payload']['title'] ?? '';
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => $request->userAgent() ?? '',
            'X-Forwarded-For' => $request->ip(),
        ];

        if ($request->hasHeader('Referer')) {
            $headers['Referer'] = $request->header('Referer');
        }

        if ($request->hasHeader('Accept-Language')) {
            $headers['Accept-Language'] = $request->header('Accept-Language');
        }

        $response = Http::withHeaders($headers)->post($baseUrl . '/api/send', $data);

        return response($response->body(), $response->status(), [
            'Content-Type' => 'application/json',
        ]);
    }
}
