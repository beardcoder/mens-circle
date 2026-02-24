<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use DOMDocument;
use Genkgo\Favicon\FullPackageGenerator;
use Genkgo\Favicon\Input;
use Genkgo\Favicon\InputImageType;
use Genkgo\Favicon\WebApplicationManifest;
use Genkgo\Favicon\WebApplicationManifestDisplay;
use Illuminate\Console\Command;

class GenerateFavicons extends Command
{
    protected $signature = 'favicon:generate
                            {--source=favicon.svg : Source image filename relative to public/}
                            {--theme-color=#3d2817 : Theme color in hex}
                            {--background-color=#3d2817 : Background color in hex}';

    protected $description = 'Generate a complete favicon package from a source image';

    public function handle(GeneralSettings $settings): int
    {
        $sourcePath = public_path($this->option('source'));
        $themeColor = $this->option('theme-color');
        $backgroundColor = $this->option('background-color');

        if (! is_file($sourcePath)) {
            $this->error("Source file not found: {$sourcePath}");

            return self::FAILURE;
        }

        $this->info('Generating favicon package from ' . basename($sourcePath) . '...');

        $input = Input::fromFile($sourcePath, InputImageType::SVG);
        $manifest = new WebApplicationManifest(
            display: WebApplicationManifestDisplay::Standalone,
            name: $settings->site_name,
            shortName: $settings->site_name,
            themeColor: $themeColor,
            backgroundColor: $backgroundColor,
        );

        $generator = FullPackageGenerator::newGenerator();
        $count = 0;

        foreach ($generator->package($input, $manifest, '/') as $fileName => $contents) {
            file_put_contents(public_path($fileName), $contents);
            $this->line("  <fg=green>✓</> {$fileName}");
            $count++;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $headHtml = '';
        foreach ($generator->headTags($document, $manifest, '/') as $tag) {
            $headHtml .= $document->saveHTML($tag) . "\n";
        }

        $partialPath = resource_path('views/partials/favicon-head.blade.php');
        file_put_contents($partialPath, $headHtml);
        $this->line('  <fg=green>✓</> resources/views/partials/favicon-head.blade.php');

        $this->newLine();
        $this->info("Generated {$count} favicon files.");

        return self::SUCCESS;
    }
}
