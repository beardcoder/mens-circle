<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\MethodCall\EloquentWhereTypeHintClosureParameterRector;
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

        // Skip rules that conflict with our modernization
        OptionalParametersAfterRequiredRector::class, // Keep our nullable params where they make sense
        EncapsedStringsToSprintfRector::class, // Prefer string interpolation over sprintf
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
        typeDeclarations: true, // Includes closure return types
        privatization: true,
        earlyReturn: true,
    )
    ->withRules([
        // PHP 8.0+ - Constructor Property Promotion
        ClassPropertyAssignToConstructorPromotionRector::class,

        // PHP 8.1+ - Readonly Properties (encourages immutability)
        ReadOnlyPropertyRector::class,

        // PHP 8.4+ - Explicit Nullable Types
        ExplicitNullableParamTypeRector::class,

        // Laravel - Eloquent Type Hints for Closures
        EloquentWhereTypeHintClosureParameterRector::class,
    ]);
