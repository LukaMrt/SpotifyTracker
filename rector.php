<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
    ])
    ->withPhpSets()
    ->withAttributesSets(
        symfony: true,
    )
    ->withComposerBased(
        symfony: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        rectorPreset: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ;
