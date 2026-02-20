<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

interface UniversalDtoMapperInterface
{
    /**
     * @template T of object
     *
     * @param array<int, array<string, scalar|null>> $rows
     * @param class-string<T> $dtoClass
     *
     * @return T[]
     */
    public function map(array $rows, string $dtoClass, string $rootId): array;
}
