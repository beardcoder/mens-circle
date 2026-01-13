<?php

declare(strict_types=1);

use PhpStaticAnalysis\RectorRule\Set\PhpStaticAnalysisAnnotationsToAttributesSetList;
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
    ])
    ->withSetProviders(LaravelSetProvider::class)
    ->withSets([
        PhpStaticAnalysisAnnotationsToAttributesSetList::ANNOTATIONS_TO_ATTRIBUTES
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: false
    );
