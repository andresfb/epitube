<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__.'/app',
            __DIR__.'/bootstrap/app.php',
            __DIR__.'/database',
            __DIR__.'/public',
        ])
        ->withSkip([
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
        ->withPhpSets();
} catch (InvalidConfigurationException $e) {
    echo 'Invalid configuration: '.$e->getMessage();
}
