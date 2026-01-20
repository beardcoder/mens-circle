<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
    ])
    ->withComposerBased(laravel: true)
    ->withSkip([
        __DIR__.'/bootstrap/cache',
        __DIR__.'/storage',
        __DIR__.'/vendor',
        __DIR__.'/.phpstorm.meta.php',
        __DIR__.'/_ide_helper.php',
    ])
    ->withSetProviders(LaravelSetProvider::class)
    ->withImportNames(removeUnusedImports: true)
    ->withPHPStanConfigs([
        __DIR__.'/phpstan.neon',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    );
