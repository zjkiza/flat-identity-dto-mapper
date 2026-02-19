<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Adapter;

use ZJKiza\FlatMapper\Collection\LazyCollection;
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;

final class CollectionAdapter implements AttributeAdapterInterface
{
    public function supports(\ReflectionProperty $property): bool
    {
        return !empty($property->getAttributes(Collection::class));
    }

    public function map(
        \ReflectionProperty $property,
        array $row,
        object $dto,
        UniversalDtoMapper $mapper
    ): void {
        $attr = $property->getAttributes(Collection::class)[0]->newInstance();

        /**
         * LAZY MODE initialized ONLY ONCE
         */
        if ($attr->lazy === true) {

            if ($property->getValue($dto) !== null) {
                return;
            }

            $rowsBuffer = [];

            $property->setValue(
                $dto,
                new LazyCollection(
                    function () use (
                        &$rowsBuffer,
                        $mapper,
                        $attr
                    ) {
                        $collection = [];

                        foreach ($rowsBuffer as $row) {
                            $item = $mapper->hydrateScalar(
                                $row,
                                $attr->className,
                                $attr->columnPrefix,
                                $attr->naming
                            );

                            if ($item === null) {
                                continue;
                            }

                            $id = $mapper->extractIdentifier($item);

                            if (!isset($collection[$id])) {
                                $collection[$id] = $item;
                            }

                            $mapper->hydrateNested($row, $collection[$id]);
                        }

                        return \array_values($collection);
                    }
                )
            );

            // IMPORTANT: we always collect rows
            $rowsBuffer[] = $row;

            return;
        }

        $item = $mapper->hydrateScalar(
            $row,
            $attr->className,
            $attr->columnPrefix,
            $attr->naming
        );

        if ($item === null) {
            return;
        }

        $collection = $property->getValue($dto) ?? [];
        $id = $mapper->extractIdentifier($item);

        if (!isset($collection[$id])) {
            $collection[$id] = $item;
        }

        /** NOW just map the NESTED objects */
        $mapper->hydrateNested($row, $collection[$id]);
        $property->setValue($dto, $collection);
    }
}
