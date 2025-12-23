@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'width' => null,
    'height' => null,
])

@php
use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;

// Get the full path to the image
$imagePath = public_path('storage/' . ltrim($src, '/'));

// Check if the image exists
if (!file_exists($imagePath)) {
    $imageExists = false;
} else {
    $imageExists = true;

    // Generate paths for modern formats
    $pathInfo = pathinfo($src);
    $baseDir = $pathInfo['dirname'];
    $filename = $pathInfo['filename'];

    // Define conversion paths
    $webpPath = $baseDir . '/' . $filename . '.webp';
    $avifPath = $baseDir . '/' . $filename . '.avif';

    $webpFullPath = public_path('storage/' . ltrim($webpPath, '/'));
    $avifFullPath = public_path('storage/' . ltrim($avifPath, '/'));

    // Ensure the directory exists
    $webpDir = dirname($webpFullPath);
    if (!is_dir($webpDir)) {
        mkdir($webpDir, 0755, true);
    }

    // Generate WebP if it doesn't exist
    if (!file_exists($webpFullPath)) {
        try {
            Image::load($imagePath)
                ->format('webp')
                ->save($webpFullPath);
        } catch (\Exception $e) {
            // If conversion fails, we'll fall back to original
            $webpPath = null;
        }
    }

    // Generate AVIF if it doesn't exist
    if (!file_exists($avifFullPath)) {
        try {
            Image::load($imagePath)
                ->format('avif')
                ->save($avifFullPath);
        } catch (\Exception $e) {
            // If conversion fails, we'll fall back to other formats
            $avifPath = null;
        }
    }
}
@endphp

@if($imageExists)
<picture>
    @if(isset($avifPath) && file_exists($avifFullPath))
    <source srcset="{{ asset('storage/' . ltrim($avifPath, '/')) }}" type="image/avif">
    @endif

    @if(isset($webpPath) && file_exists($webpFullPath))
    <source srcset="{{ asset('storage/' . ltrim($webpPath, '/')) }}" type="image/webp">
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
