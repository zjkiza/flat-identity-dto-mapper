<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Attribute;

use ZJKiza\FlatMapper\Contract\AttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Column implements AttributeInterface
{
    public function __construct(public string $name)
    {
    }
}
