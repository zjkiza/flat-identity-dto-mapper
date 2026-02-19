<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

use ZJKiza\FlatMapper\UniversalDtoMapper;

interface AttributeAdapterInterface
{
    public function supports(\ReflectionProperty $property): bool;

    public function map(
        \ReflectionProperty $property,
        array $row,
        object $dto,
        UniversalDtoMapper $mapper
    ): void;
}
