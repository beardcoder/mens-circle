<?php

namespace App\Helpers;

class ViteHelper
{
    /**
     * Generate preload tags for fonts from Vite manifest
     *
     * @param  array  $fontPaths  Array of font paths relative to resources directory
     * @return string HTML preload tags
     */
    public static function preloadFonts(array $fontPaths = []): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (! file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(
            file_get_contents($manifestPath),
            true
        );

        if (! $manifest) {
            return '';
        }

        $preloadTags = [];

        foreach ($fontPaths as $fontPath) {
            // Check if this asset exists in manifest
            if (isset($manifest[$fontPath])) {
                $file = $manifest[$fontPath]['file'];
                $preloadTags[] = sprintf(
                    '<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>',
                    asset("build/{$file}")
                );
            }
        }

        return implode("\n    ", $preloadTags);
    }
}
