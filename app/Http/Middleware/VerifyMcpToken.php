<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $secret = config('services.mcp.secret');

        if (!$secret || !$token || !hash_equals((string) $secret, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
