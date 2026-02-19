<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Attribute;

use ZJKiza\FlatMapper\Contract\AttributeInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ColumnPrefix implements AttributeInterface
{
    public function __construct(
        public string $name,
        public string $naming = 'snakeToCamel'
    ) {
    }
}
