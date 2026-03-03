<?php

declare(strict_types=1);

namespace App\Traits;

use App\Contracts\DefinesCacheUrls;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * @phpstan-require-implements DefinesCacheUrls
 */
trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        $clearCache = static function (self $model): void {
            $urls = $model->getCacheUrls();

            if ($urls !== []) {
                ResponseCache::forget($urls);
            }

            cache()->forget('next_event_data');
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }
}
