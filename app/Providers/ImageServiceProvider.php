<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Intervention\Image\ImageManager;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, function ($app) {
            $driver = match (config('image.driver', 'vips')) {
                'gd' => new GdDriver(),
                'imagick' => new ImagickDriver(),
                default => new VipsDriver(),
            };

            return new ImageManager($driver);
        });

        $this->app->alias(ImageManager::class, 'image');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade directive for responsive images
        Blade::directive('responsiveImage', function ($expression) {
            return "<?php echo app('App\Services\ImageService')->responsiveImage({$expression}); ?>";
        });

        // Register Blade directive for image optimization
        Blade::directive('optimizeImage', function ($expression) {
            return "<?php echo app('App\Services\ImageService')->optimize({$expression}); ?>";
        });
    }
}
