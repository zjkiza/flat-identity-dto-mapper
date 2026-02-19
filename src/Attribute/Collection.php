<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Attribute;

use ZJKiza\FlatMapper\Contract\AttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Collection implements AttributeInterface
{
    public function __construct(
        public string $className,
        public string $columnPrefix,
        public string $naming = 'snakeToCamel',
        public bool $lazy = false,
    ) {
    }
}
