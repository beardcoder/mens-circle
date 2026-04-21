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
        if (auth()->check() || $this->isValidBearerToken(config('services.ai_management.token'), $request->bearerToken())) {
            return $next($request);
        }

        return new JsonResponse([
            'message' => 'Nicht autorisiert für die KI-Verwaltung.',
        ], 401);
    }

    private function isValidBearerToken(mixed $configuredToken, ?string $bearerToken): bool
    {
        return is_string($configuredToken)
            && $configuredToken !== ''
            && is_string($bearerToken)
            && hash_equals($configuredToken, $bearerToken);
    }
}
