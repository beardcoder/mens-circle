<?php

declare(strict_types=1);

namespace App\Traits;

use App\Contracts\DefinesCacheUrls;
use Illuminate\Support\Facades\Log;
use Spatie\ResponseCache\Facades\ResponseCache;
use Spatie\Varnish\Varnish;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
                self::flushVarnishUrls($urls);
            }

            if (\in_array($model->getTable(), ['events', 'registrations'], true)) {
                cache()->forget('next_event_data');
            }
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    /**
     * Ban specific URL paths from Varnish cache.
     *
     * @param list<string> $urls
     */
    private static function flushVarnishUrls(array $urls): void
    {
        if (! config('varnish.host') || app()->runningUnitTests()) {
            return;
        }

        $paths = array_map(
            static fn(string $url): string => preg_quote(parse_url($url, PHP_URL_PATH) ?: '/', '/'),
            $urls,
        );

        $urlRegex = '^(' . implode('|', $paths) . ')$';

        try {
            (new Varnish())->flush(url: $urlRegex);
        } catch (ProcessFailedException $processFailedException) {
            Log::warning('Varnish cache flush failed', [
                'urls' => $urls,
                'error' => $processFailedException->getMessage(),
            ]);
        }
    }
}
