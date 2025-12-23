@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'width' => null,
    'height' => null,
])

@php
use Illuminate\Support\Facades\Cache;
use Spatie\Image\Image;

$imagePath = public_path('storage/' . ltrim($src, '/'));
$imageExists = file_exists($imagePath);
$formats = [];

if ($imageExists) {
    // Skip optimization for SVGs
    if (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) !== 'svg') {
        // Cache the existence/generation of optimized formats to avoid disk I/O on every request
        // Key includes filemtime to automatically regenerate if source image changes
        $cacheKey = 'modern_image_' . md5($src . filemtime($imagePath));

        $formats = Cache::remember($cacheKey, now()->addMonth(), function () use ($src, $imagePath) {
            $pathInfo = pathinfo($src);
            $baseDir = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $results = [];

            $generate = function($format) use ($imagePath, $baseDir, $filename) {
                $newPath = $baseDir . '/' . $filename . '.' . $format;
                $fullPath = public_path('storage/' . ltrim($newPath, '/'));
                
                if (!file_exists($fullPath)) {
                    try {
                        if (!is_dir(dirname($fullPath))) {
                            mkdir(dirname($fullPath), 0755, true);
                        }
                        Image::load($imagePath)->format($format)->save($fullPath);
                    } catch (\Exception $e) {
                        return null;
                    }
                }
                return $newPath;
            };

            $results['webp'] = $generate('webp');
            $results['avif'] = $generate('avif');

            return $results;
        });
    }
}
@endphp

@if($imageExists)
    @if(empty($formats))
        <img
            src="{{ asset('storage/' . ltrim($src, '/')) }}"
            alt="{{ $alt }}"
            decoding="async"
            @if($class) class="{{ $class }}" @endif
            @if($loading) loading="{{ $loading }}" @endif
            @if($width) width="{{ $width }}" @endif
            @if($height) height="{{ $height }}" @endif
            {{ $attributes }}
        >
    @else
        <picture>
            @if(!empty($formats['avif']))
            <source srcset="{{ asset('storage/' . ltrim($formats['avif'], '/')) }}" type="image/avif">
            @endif

            @if(!empty($formats['webp']))
            <source srcset="{{ asset('storage/' . ltrim($formats['webp'], '/')) }}" type="image/webp">
            @endif

            <img
                src="{{ asset('storage/' . ltrim($src, '/')) }}"
                alt="{{ $alt }}"
                decoding="async"
                @if($class) class="{{ $class }}" @endif
                @if($loading) loading="{{ $loading }}" @endif
                @if($width) width="{{ $width }}" @endif
                @if($height) height="{{ $height }}" @endif
                {{ $attributes }}
            >
        </picture>
    @endif
@endif
