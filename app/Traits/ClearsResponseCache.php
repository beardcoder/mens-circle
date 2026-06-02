<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        static::saved(static function (): void {
            ResponseCache::clear();
        });
        static::deleted(static function (): void {
            ResponseCache::clear();
        });
    }
}
