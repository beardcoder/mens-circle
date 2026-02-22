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
}
