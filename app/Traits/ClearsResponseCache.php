<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        $clearCache = function (): void {
            ResponseCache::clear();
            cache()
                ->forget('has_next_event');
        };

        self::created($clearCache);
        self::updated($clearCache);
        self::deleted($clearCache);
    }
}
