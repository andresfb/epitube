<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;
use RectorLaravel\Set\LaravelSetProvider;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__.'/app',
            __DIR__.'/modules',
            __DIR__.'/bootstrap/app.php',
            __DIR__.'/database',
            __DIR__.'/public',
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            typeDeclarations: true,
            privatization: true,
            earlyReturn: true,
        )
        ->withPhpSets()
        ->withSetProviders(LaravelSetProvider::class)
        ->withComposerBased(laravel: true/** other options */)
        ->withSkip([
            AddOverrideAttributeToOverriddenMethodsRector::class,
            DisallowedEmptyRuleFixerRector::class,
            ClosureToArrowFunctionRector::class,
            AddClosureVoidReturnTypeWhereNoReturnRector::class,
            AddFunctionVoidReturnTypeWhereNoReturnRector::class,
            NullToStrictStringFuncCallArgRector::class,
        ])
        ->withImportNames(removeUnusedImports: true);
} catch (InvalidConfigurationException $e) {
    echo 'Invalid configuration: '.$e->getMessage();
}
