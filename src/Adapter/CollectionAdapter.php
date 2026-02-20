<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Adapter;

use ZJKiza\FlatMapper\Collection\LazyCollection;
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;

/**
 * @psalm-suppress UnsupportedPropertyReferenceUsage
 */
final class CollectionAdapter implements AttributeAdapterInterface
{
    /**
     * @var array<int, array<string, list<array<string, scalar|null>>>>
     *
     * [dtoObjectId][propertyName] => rowsBuffer
     */
    private static array $buffers = [];

    public function supports(\ReflectionProperty $property): bool
    {
        return (bool)$property->getAttributes(Collection::class);
    }

    /**
     * @param array<string, scalar|null> $row
     */
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
            $dtoId = \spl_object_id($dto);
            $propName = $property->getName();

            // Buffer initialization with explicit type checking
            if (!isset(self::$buffers[$dtoId])) {
                self::$buffers[$dtoId] = [];
            }

            if (!isset(self::$buffers[$dtoId][$propName])) {
                self::$buffers[$dtoId][$propName] = [];
            }

            /** @var list<array<string, scalar|null>> $buffer */
            $buffer = &self::$buffers[$dtoId][$propName];
            $buffer[] = $row;

            // LazyCollection se kreira samo jednom
            if (null === $property->getValue($dto)) {
                $property->setValue(
                    $dto,
                    new LazyCollection(
                        function () use ($dtoId, $propName, $mapper, $attr): array {
                            /** @var list<array<string, scalar|null>> $rows */
                            $rows = self::$buffers[$dtoId][$propName] ?? [];
                            $collection = [];

                            foreach ($rows as $row) {
                                /** @var array<string, scalar|null> $row */
                                $item = $mapper->hydrateScalar(
                                    $row,
                                    $attr->className,
                                    $attr->columnPrefix,
                                    $attr->naming
                                );

                                if (null === $item) {
                                    continue;
                                }

                                $id = $mapper->extractIdentifier($item);

                                if (!isset($collection[$id])) {
                                    $collection[$id] = $item;
                                }

                                $collectionItem = $collection[$id];
                                $mapper->hydrateNested($row, $collectionItem);
                            }

                            // cleanup buffer
                            unset(self::$buffers[$dtoId][$propName]);

                            if (false === (bool)self::$buffers[$dtoId]) {
                                unset(self::$buffers[$dtoId]);
                            }

                            return \array_values($collection);
                        }
                    )
                );
            }

            return;
        }

        $item = $mapper->hydrateScalar(
            $row,
            $attr->className,
            $attr->columnPrefix,
            $attr->naming
        );

        if (null === $item) {
            return;
        }

        /** @var array<string, object>|null $collection */
        $collection = $property->getValue($dto);

        if (null === $collection) {
            $collection = [];
        }

        $id = $mapper->extractIdentifier($item);

        if (!isset($collection[$id])) {
            $collection[$id] = $item;
        }

        /** @var object $collectionItem */
        $collectionItem = $collection[$id];
        $mapper->hydrateNested($row, $collectionItem);
        $property->setValue($dto, $collection);
    }
}
