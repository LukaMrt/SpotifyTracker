<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        __DIR__ . '/migrations',
        // Skip auto-generated files
        __DIR__ . '/src/Kernel.php',
        // Skip specific rules for tests
        AddVoidReturnTypeWhereNoReturnRector::class => [
            __DIR__ . '/tests',
        ],
    ])
    ->withPhpSets(
        php83: true
    )
    ->withAttributesSets(
        symfony: true,
        phpunit: true,
    )
    ->withComposerBased(
        phpunit: true,
        symfony: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSets([
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_120,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    // Remove duplicate rules that are already in prepared sets
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withParallel(
        timeoutSeconds: 300,
        maxNumberOfProcess: 4,
        jobSize: 20
    )
    ;
