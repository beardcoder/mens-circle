# Image Integration with Intervention Image and libvips

This application uses [Intervention Image v3](https://image.intervention.io/) with libvips driver for high-performance image processing and optimization.

## Features

- **Automatic format conversion** to WebP and AVIF for modern browsers
- **libvips driver** for superior performance and memory efficiency
- **Responsive images** with `<picture>` element support
- **Lazy loading** by default
- **Image caching** to avoid regenerating optimized versions
- **Configurable quality** settings per format
- **Multiple driver support** (vips, imagick, gd)

## Configuration

Configuration file: `config/image.php`

### Available Options

```php
return [
    // Image driver: 'vips' (recommended), 'imagick', or 'gd'
    'driver' => env('IMAGE_DRIVER', 'vips'),

    // Quality settings (0-100)
    'quality' => [
        'webp' => env('IMAGE_QUALITY_WEBP', 85),
        'avif' => env('IMAGE_QUALITY_AVIF', 85),
        'jpeg' => env('IMAGE_QUALITY_JPEG', 85),
    ],

    // Cache duration in seconds (default: 30 days)
    'cache_duration' => env('IMAGE_CACHE_DURATION', 2592000),

    // Which formats to auto-generate
    'formats' => [
        'webp' => env('IMAGE_GENERATE_WEBP', true),
        'avif' => env('IMAGE_GENERATE_AVIF', true),
    ],
];
```

### Environment Variables

You can override these in your `.env` file:

```env
IMAGE_DRIVER=vips
IMAGE_QUALITY_WEBP=85
IMAGE_QUALITY_AVIF=85
IMAGE_QUALITY_JPEG=85
IMAGE_CACHE_DURATION=2592000
IMAGE_GENERATE_WEBP=true
IMAGE_GENERATE_AVIF=true
```

## Usage

### 1. Blade Component (Recommended)

The `<x-modern-image>` component automatically generates responsive images:

```blade
<x-modern-image
    src="images/hero.jpg"
    alt="Hero Image"
    class="w-full h-auto"
    loading="lazy"
    width="1200"
    height="800"
/>
```

This generates:

```html
<picture>
    <source srcset="/storage/images/hero.avif" type="image/avif">
    <source srcset="/storage/images/hero.webp" type="image/webp">
    <img
        src="/storage/images/hero.jpg"
        alt="Hero Image"
        class="w-full h-auto"
        loading="lazy"
        width="1200"
        height="800"
        decoding="async"
    >
</picture>
```

### 2. Helper Functions

#### responsive_image()

```php
// Generate responsive image HTML
echo responsive_image(
    src: 'images/photo.jpg',
    alt: 'Description',
    class: 'rounded-lg',
    loading: 'lazy',
    width: 800,
    height: 600,
    attributes: ['data-gallery' => 'true']
);
```

#### optimize_image()

```php
// Optimize to WebP
$webpPath = optimize_image('images/photo.jpg', 'webp');

// Optimize to AVIF with custom quality
$avifPath = optimize_image('images/photo.jpg', 'avif', 75);
```

### 3. ImageService (Advanced)

For more control, inject the `ImageService`:

```php
use App\Services\ImageService;

public function processImage(ImageService $imageService)
{
    // Generate responsive image
    $html = $imageService->responsiveImage(
        src: 'images/hero.jpg',
        alt: 'Hero Image',
        class: 'hero-img'
    );

    // Optimize to specific format
    $webpPath = $imageService->optimize('images/photo.jpg', 'webp');

    // Resize image
    $resizedPath = $imageService->resize(
        src: 'images/photo.jpg',
        width: 800,
        height: 600,
        aspectRatio: true
    );

    // Clear cache for an image
    $imageService->clearCache('images/photo.jpg');
}
```

### 4. Direct ImageManager Usage

For advanced image manipulation:

```php
use Intervention\Image\ImageManager;

public function manipulateImage(ImageManager $manager)
{
    $image = $manager->read(public_path('storage/images/photo.jpg'));

    // Apply transformations
    $image->scale(width: 800)
          ->blur(10)
          ->greyscale()
          ->toWebp(85)
          ->save(public_path('storage/images/photo-processed.webp'));
}
```

Or using the helper:

```php
$image = image()->read(public_path('storage/images/photo.jpg'));
```

### 5. Blade Directives

```blade
{{-- Generate responsive image --}}
@responsiveImage('images/photo.jpg', 'Description', 'w-full')

{{-- Optimize single image --}}
@optimizeImage('images/photo.jpg', 'webp')
```

## How It Works

1. **First Request**: When an image is requested, the system:
   - Checks if optimized versions (WebP, AVIF) exist
   - If not, generates them using libvips
   - Caches the file paths for future requests

2. **Subsequent Requests**:
   - Returns cached paths instantly
   - No regeneration unless source image changes
   - Cache invalidates automatically on source file modification

3. **SVG Handling**: SVG files are served as-is without optimization

## Performance Benefits

### libvips vs. GD/Imagick

- **Speed**: 4-8x faster than ImageMagick, 8-15x faster than GD
- **Memory**: Uses streaming, processes images in chunks
- **Quality**: Superior output quality, especially for WebP/AVIF
- **Parallel Processing**: Multi-threaded by default

### Benchmark Example

Processing a 5MB JPEG to WebP:
- **GD**: ~2.5s, 450MB RAM
- **Imagick**: ~1.2s, 280MB RAM
- **libvips**: ~0.3s, 45MB RAM

## Common Patterns

### Hero Images with Background

```blade
@php
$bgImage = $block['background_image'] ?? null;
@endphp

<section class="hero" @if($bgImage) style="background-image: url({{ asset('storage/' . optimize_image($bgImage, 'webp')) }});" @endif>
    <h1>{{ $block['title'] }}</h1>
</section>
```

### Moderator Photos

```blade
<x-modern-image
    :src="$moderator['photo']"
    :alt="$moderator['name']"
    class="moderator-photo rounded-full"
    width="400"
    height="400"
/>
```

### Gallery with Thumbnails

```php
// In controller or service
$thumbnail = $imageService->resize('gallery/photo.jpg', 300, 300);
$webp = $imageService->optimize($thumbnail, 'webp');
```

## Troubleshooting

### Images Not Generating

1. Check storage permissions: `chmod -R 775 storage/app/public`
2. Ensure symbolic link exists: `php artisan storage:link`
3. Verify libvips is installed: `vips --version`
4. Check logs: `tail -f storage/logs/laravel.log`

### libvips Not Available

If libvips isn't available, fall back to imagick or gd:

```env
IMAGE_DRIVER=imagick  # or 'gd'
```

### Cache Issues

Clear image cache:

```php
use Illuminate\Support\Facades\Cache;

// Clear all image caches
Cache::flush();

// Or clear specific image
image_service()->clearCache('images/photo.jpg');
```

## Docker Setup

The Dockerfile includes libvips installation:

```dockerfile
# Install system dependencies including libvips
RUN apk add --no-cache \
    vips \
    vips-dev \
    vips-tools

# Install PHP vips extension
RUN install-php-extensions vips
```

## Testing

```php
use App\Services\ImageService;

test('generates responsive images', function () {
    $service = app(ImageService::class);

    $html = $service->responsiveImage(
        src: 'test-images/photo.jpg',
        alt: 'Test'
    );

    expect($html)
        ->toContain('<picture>')
        ->toContain('image/webp')
        ->toContain('image/avif');
});
```

## Resources

- [Intervention Image Documentation](https://image.intervention.io/v3)
- [libvips Documentation](https://www.libvips.org/)
- [WebP Guide](https://developers.google.com/speed/webp)
- [AVIF Guide](https://jakearchibald.com/2020/avif-has-landed/)
