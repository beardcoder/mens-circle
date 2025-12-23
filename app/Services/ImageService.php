<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;

class ImageService
{
    public function __construct(protected ImageManager $manager)
    {
    }

    /**
     * Generate optimized image formats (WebP, AVIF) and return picture element HTML
     */
    public function responsiveImage(
        string $src,
        string $alt = '',
        string $class = '',
        string $loading = 'lazy',
        ?int $width = null,
        ?int $height = null,
        array $attributes = []
    ): string {
        $imagePath = public_path('storage/'.ltrim($src, '/'));

        if (! file_exists($imagePath)) {
            return '';
        }

        // Skip optimization for SVGs
        if (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'svg') {
            return $this->buildImgTag($src, $alt, $class, $loading, $width, $height, $attributes);
        }

        // Cache the existence/generation of optimized formats to avoid disk I/O on every request
        $cacheKey = 'modern_image_'.md5($src.filemtime($imagePath));
        $cacheDuration = config('image.cache_duration', 2592000);

        $formats = Cache::remember($cacheKey, now()->addSeconds($cacheDuration), function () use ($src, $imagePath) {
            return $this->generateFormats($src, $imagePath);
        });

        if (empty($formats)) {
            return $this->buildImgTag($src, $alt, $class, $loading, $width, $height, $attributes);
        }

        return $this->buildPictureTag($src, $formats, $alt, $class, $loading, $width, $height, $attributes);
    }

    /**
     * Optimize a single image and return optimized path
     */
    public function optimize(string $src, string $format = 'webp', ?int $quality = null): ?string
    {
        $imagePath = public_path('storage/'.ltrim($src, '/'));

        if (! file_exists($imagePath)) {
            return null;
        }

        // Use quality from config if not specified
        if ($quality === null) {
            $quality = config("image.quality.{$format}", 85);
        }

        $pathInfo = pathinfo($src);
        $baseDir = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        $newPath = $baseDir.'/'.$filename.'.'.$format;
        $fullPath = public_path('storage/'.ltrim($newPath, '/'));

        if (! file_exists($fullPath)) {
            try {
                if (! is_dir(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }

                $image = $this->manager->read($imagePath);

                match ($format) {
                    'webp' => $image->toWebp($quality),
                    'avif' => $image->toAvif($quality),
                    'jpg', 'jpeg' => $image->toJpeg($quality),
                    'png' => $image->toPng(),
                    default => $image,
                };

                $image->save($fullPath);
            } catch (\Exception $e) {
                report($e);
                return null;
            }
        }

        return $newPath;
    }

    /**
     * Generate multiple image formats
     */
    protected function generateFormats(string $src, string $imagePath): array
    {
        $results = [];

        if (config('image.formats.webp', true)) {
            $results['webp'] = $this->optimize($src, 'webp');
        }

        if (config('image.formats.avif', true)) {
            $results['avif'] = $this->optimize($src, 'avif');
        }

        return array_filter($results);
    }

    /**
     * Build a simple img tag
     */
    protected function buildImgTag(
        string $src,
        string $alt,
        string $class,
        string $loading,
        ?int $width,
        ?int $height,
        array $attributes
    ): string {
        $attrs = [];
        $attrs[] = 'src="'.asset('storage/'.ltrim($src, '/')).'"';
        $attrs[] = 'alt="'.e($alt).'"';
        $attrs[] = 'decoding="async"';

        if ($class) {
            $attrs[] = 'class="'.e($class).'"';
        }
        if ($loading) {
            $attrs[] = 'loading="'.e($loading).'"';
        }
        if ($width) {
            $attrs[] = 'width="'.(int) $width.'"';
        }
        if ($height) {
            $attrs[] = 'height="'.(int) $height.'"';
        }

        foreach ($attributes as $key => $value) {
            $attrs[] = e($key).'="'.e($value).'"';
        }

        return '<img '.implode(' ', $attrs).'>';
    }

    /**
     * Build a picture tag with multiple sources
     */
    protected function buildPictureTag(
        string $src,
        array $formats,
        string $alt,
        string $class,
        string $loading,
        ?int $width,
        ?int $height,
        array $attributes
    ): string {
        $sources = [];

        if (! empty($formats['avif'])) {
            $sources[] = '<source srcset="'.asset('storage/'.ltrim($formats['avif'], '/')).'" type="image/avif">';
        }

        if (! empty($formats['webp'])) {
            $sources[] = '<source srcset="'.asset('storage/'.ltrim($formats['webp'], '/')).'" type="image/webp">';
        }

        $img = $this->buildImgTag($src, $alt, $class, $loading, $width, $height, $attributes);

        return '<picture>'.implode('', $sources).$img.'</picture>';
    }

    /**
     * Resize an image to specific dimensions
     */
    public function resize(string $src, int $width, ?int $height = null, bool $aspectRatio = true): ?string
    {
        $imagePath = public_path('storage/'.ltrim($src, '/'));

        if (! file_exists($imagePath)) {
            return null;
        }

        $pathInfo = pathinfo($src);
        $baseDir = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        $newPath = $baseDir.'/'.$filename.'-'.$width.'x'.($height ?? 'auto').'.'.$extension;
        $fullPath = public_path('storage/'.ltrim($newPath, '/'));

        if (! file_exists($fullPath)) {
            try {
                if (! is_dir(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }

                $image = $this->manager->read($imagePath);

                if ($aspectRatio) {
                    $image->scale($width, $height);
                } else {
                    $image->resize($width, $height);
                }

                $image->save($fullPath);
            } catch (\Exception $e) {
                report($e);
                return null;
            }
        }

        return $newPath;
    }

    /**
     * Clear cached image data
     */
    public function clearCache(string $src): void
    {
        $imagePath = public_path('storage/'.ltrim($src, '/'));

        if (file_exists($imagePath)) {
            $cacheKey = 'modern_image_'.md5($src.filemtime($imagePath));
            Cache::forget($cacheKey);
        }
    }
}
