<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAiAccess
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.ai_management.token');
        $bearerToken = $request->bearerToken();
        $hasToken = is_string($configuredToken) && $configuredToken !== '' && is_string($bearerToken) && hash_equals($configuredToken, $bearerToken);

        if (auth()->check() || $hasToken) {
            return $next($request);
        }

        return new JsonResponse([
            'message' => 'Nicht autorisiert für die KI-Verwaltung.',
        ], 401);
    }
}
