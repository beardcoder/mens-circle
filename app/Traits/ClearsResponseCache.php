<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        $clearCache = static function (): void {
            ResponseCache::clear();
            cache()
                ->forget('has_next_event');
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }
}
