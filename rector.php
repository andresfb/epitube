<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__.'/',
        ])
        ->withSkip([
            __DIR__.'/bootstrap/cache',
            __DIR__.'/storage',
            __DIR__.'/node_modules',
            __DIR__.'/vendor',
            __DIR__.'/deploy.php',
            __DIR__.'/rector.php',
            AddOverrideAttributeToOverriddenMethodsRector::class,
            DisallowedEmptyRuleFixerRector::class,
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            typeDeclarations: true,
            privatization: true,
            earlyReturn: true,
            strictBooleans: true,
        )
        ->withPhpSets(
            php83: true,
        );
} catch (InvalidConfigurationException $e) {
    echo 'Invalid configuration: '.$e->getMessage();
}
