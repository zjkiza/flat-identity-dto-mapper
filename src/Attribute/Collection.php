<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Attribute;

use ZJKiza\FlatMapper\Contract\AttributeInterface;
use ZJKiza\FlatMapper\Enum\Naming;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Collection implements AttributeInterface
{
    /**
     * @param class-string $className
     * @param string $columnPrefix
     * @param Naming $naming
     * @param bool $lazy
     */
    public function __construct(
        public string $className,
        public string $columnPrefix,
        public Naming $naming = Naming::CamelToSnake,
        public bool $lazy = false,
    ) {
    }
}
