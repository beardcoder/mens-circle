<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        $clear = ResponseCache::clear(...);

        static::saved($clear);
        static::deleted($clear);
    }
}
