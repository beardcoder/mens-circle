<?php

declare(strict_types=1);

namespace App\Support\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Response;

class OctaneCacheProfile implements CacheProfile
{
    public function enabled(Request $request): bool
    {
        // Cache ist aktiviert wenn:
        // 1. Die Config es erlaubt
        // 2. Es ist ein GET Request
        // 3. Es ist kein Filament Admin Request
        // 4. User ist nicht eingeloggt (optional)
        return config('responsecache.enabled')
            && $request->isMethod('GET')
            && ! str_starts_with($request->path(), 'admin')
            && ! str_starts_with($request->path(), 'filament');
    }

    public function shouldCacheRequest(Request $request): bool
    {
        // Gleiche Bedingungen wie enabled()
        return $this->enabled($request);
    }

    public function shouldCacheResponse(Response $response): bool
    {
        // Nur erfolgreiche Responses cachen (2xx Status Codes)
        return $response->isSuccessful() || $response->isRedirection();
    }

    public function useCacheNameSuffix(Request $request): string
    {
        // Verwende Suffix wenn User eingeloggt ist
        return auth()->check() ? 'yes' : '';
    }

    public function cacheNameSuffix(Request $request): string
    {
        // Füge User-ID hinzu falls eingeloggt (für personalisierte Caches)
        $suffix = '';

        if (auth()->check()) {
            $suffix .= '-user-'.auth()->id();
        }

        return $suffix;
    }

    public function cacheRequestUntil(Request $request): \DateTime
    {
        // Verwende die Standard-Lifetime aus der Config
        $lifetime = (int) config('responsecache.cache_lifetime_in_seconds', 60 * 60 * 24 * 7);

        return (new \DateTime())->modify("+{$lifetime} seconds");
    }
}
