<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(ClassPropertyAssignToConstructorPromotionRector::class);
    $rectorConfig->rule(\Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector::class);

    //define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
    ]);

    // These docblocks/casts are intentional: Psalm/PHPStan rely on them to narrow generic types.
    $rectorConfig->skip([
        \Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector::class => [
            __DIR__ . '/src/Metadata/MetadataFactory.php',
            __DIR__ . '/src/UniversalDtoMapper.php',
        ],
        \Rector\DeadCode\Rector\Cast\RecastingRemovalRector::class => [
            __DIR__ . '/src/Strategy/SnakeToCamelStrategy.php',
        ],
    ]);
};
