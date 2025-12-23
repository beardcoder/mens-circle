<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateHeroiconEnum extends Command
{
    protected $signature = 'generate:heroicon-enum {--force : Overwrite existing enum file}';

    protected $description = 'Generate Heroicon enum from Iconify JSON dataset';

    private const HEROICONS_JSON_URL = 'https://raw.githubusercontent.com/iconify/icon-sets/refs/heads/master/json/heroicons.json';

    private const ENUM_PATH = 'app/Enums/Heroicon.php';

    public function handle(): int
    {
        $this->info('Fetching Heroicons JSON from Iconify...');

        $response = Http::timeout(30)->get(self::HEROICONS_JSON_URL);

        if ($response->failed()) {
            $this->error('Failed to fetch Heroicons JSON');

            return self::FAILURE;
        }

        $data = $response->json();

        if (! isset($data['icons'])) {
            $this->error('Invalid JSON structure: missing "icons" key');

            return self::FAILURE;
        }

        $this->info('Parsing icons...');

        $icons = $data['icons'];
        $iconCount = count($icons);

        $this->info("Found {$iconCount} icons");

        $this->info('Generating enum...');

        $enumContent = $this->generateEnumContent($icons, $data);

        $enumPath = base_path(self::ENUM_PATH);

        if (file_exists($enumPath) && ! $this->option('force')) {
            if (! $this->confirm('Enum file already exists. Overwrite?')) {
                $this->warn('Operation cancelled');

                return self::FAILURE;
            }
        }

        file_put_contents($enumPath, $enumContent);

        $this->info('✓ Heroicon enum generated successfully at '.self::ENUM_PATH);
        $this->info("✓ Generated {$iconCount} icon cases");

        return self::SUCCESS;
    }

    private function generateEnumContent(array $icons, array $data): string
    {
        $cases = [];
        $svgMethods = [];

        $width = $data['width'] ?? 24;
        $height = $data['height'] ?? 24;

        foreach ($icons as $name => $iconData) {
            $enumCase = $this->convertToEnumCase($name);
            $cases[] = "    case {$enumCase} = '{$name}';";

            $body = $iconData['body'] ?? '';
            $svgMethods[] = "            self::{$enumCase} => '{$body}',";
        }

        $casesString = implode("\n", $cases);
        $svgMethodsString = implode("\n", $svgMethods);

        return <<<PHP
<?php

namespace App\Enums;

enum Heroicon: string
{
{$casesString}

    public function getSvg(string|int \$size = 24, ?string \$class = null): string
    {
        \$classAttr = \$class ? " class=\"{\$class}\"" : '';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 {$width} {$height}" stroke-width="1.5" stroke="currentColor" width="%s" height="%s"%s>%s</svg>',
            \$size,
            \$size,
            \$classAttr,
            \$this->getBody()
        );
    }

    public function getBody(): string
    {
        return match (\$this) {
{$svgMethodsString}
        };
    }

    public function getName(): string
    {
        return \$this->value;
    }

    public static function fromName(string \$name): ?self
    {
        return self::tryFrom(\$name);
    }
}

PHP;
    }

    private function convertToEnumCase(string $iconName): string
    {
        // Convert icon name like "academic-cap" to "ACADEMIC_CAP"
        // or "20-solid/academic-cap" to "S20_ACADEMIC_CAP"

        // Handle variant prefixes (16-solid, 20-solid, 24-outline, 24-solid, mini)
        if (preg_match('/^(\d+)-(solid|outline)\/(.+)$/', $iconName, $matches)) {
            $size = $matches[1];
            $variant = $matches[2];
            $name = $matches[3];

            $prefix = strtoupper($variant[0]).$size; // S20, O24, etc.

            return $prefix.'_'.strtoupper(str_replace('-', '_', $name));
        }

        if (preg_match('/^mini\/(.+)$/', $iconName, $matches)) {
            $name = $matches[1];

            return 'MINI_'.strtoupper(str_replace('-', '_', $name));
        }

        // Default: just convert the name
        return strtoupper(str_replace('-', '_', $iconName));
    }
}
