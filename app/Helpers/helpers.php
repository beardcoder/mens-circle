<?php

use App\Models\Setting;
use App\Services\ImageService;
use Intervention\Image\ImageManager;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return cache()->remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }
}

if (! function_exists('settings')) {
    function settings(): array
    {
        return cache()->remember('settings_all', 3600, function () {
            $settings = Setting::all();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }

            return $result;
        });
    }
}

if (! function_exists('image')) {
    /**
     * Get the ImageManager instance
     */
    function image(): ImageManager
    {
        return app(ImageManager::class);
    }
}

if (! function_exists('image_service')) {
    /**
     * Get the ImageService instance
     */
    function image_service(): ImageService
    {
        return app(ImageService::class);
    }
}

if (! function_exists('responsive_image')) {
    /**
     * Generate a responsive image with multiple formats and srcset
     *
     * @param  array<string, mixed>  $attributes
     */
    function responsive_image(
        string $src,
        string $alt = '',
        string $class = '',
        string $loading = 'lazy',
        ?int $width = null,
        ?int $height = null,
        array $attributes = [],
        ?string $sizes = null
    ): string {
        return image_service()->responsiveImage($src, $alt, $class, $loading, $width, $height, $attributes, $sizes);
    }
}

if (! function_exists('optimize_image')) {
    /**
     * Optimize an image to a specific format
     */
    function optimize_image(string $src, string $format = 'webp', ?int $quality = null): ?string
    {
        return image_service()->optimize($src, $format, $quality);
    }
}
