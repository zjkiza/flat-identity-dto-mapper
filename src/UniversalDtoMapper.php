<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper;

use ZJKiza\FlatMapper\Adapter\CollectionAdapter;
use ZJKiza\FlatMapper\Adapter\ObjectAdapter;
use ZJKiza\FlatMapper\Attribute\Column;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Transformer as TransformerAttribute;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;
use ZJKiza\FlatMapper\Contract\TransformerInterface;
use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Identity\IdentityMap;
use ZJKiza\FlatMapper\Metadata\MetadataFactory;
use ZJKiza\FlatMapper\Strategy\NamingStrategyFactory;
use ZJKiza\FlatMapper\Transformer\Transformer;

final class UniversalDtoMapper
{
    /** @var AttributeAdapterInterface[] */
    private array $adapters;

    private IdentityMap $identityMap;

    private TransformerInterface $transformer;

    public function __construct(?TransformerInterface $transformer = null)
    {
        $this->adapters = [
            new ObjectAdapter(),
            new CollectionAdapter(),
        ];

        $this->transformer = $transformer ?? new Transformer();
    }

    public function map(array $rows, string $dtoClass, string $rootId): array
    {
        $this->identityMap = new IdentityMap();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row[$rootId]][] = $row;
        }

        // Freeing memory - the original $rows are no longer needed
        unset($rows);

        $result = [];

        foreach ($grouped as $rowsGroup) {
            $dto = null;

            foreach ($rowsGroup as $row) {
                if ($dto === null) {
                    $dto = $this->hydrateScalar($row, $dtoClass);
                }

                $this->hydrateNested($row, $dto);
            }

            $result[] = $dto;
        }

        return $result;
    }

    public function hydrateScalar(
        array   $row,
        string  $dtoClass,
        ?string $forcedPrefix = null,
        ?string $forcedNaming = null
    ): ?object {
        $meta = MetadataFactory::get($dtoClass);
        $dto = $meta->reflectionClass->newInstanceWithoutConstructor();

        $classPrefixAttr = $meta->reflectionClass->getAttributes(ColumnPrefix::class)[0] ?? null;
        $classPrefix = $classPrefixAttr?->newInstance();

        $prefix = $forcedPrefix ?? ($classPrefix?->name ?? '');
        $naming = $forcedNaming ?? ($classPrefix?->naming ?? 'snakeToCamel');
        $namer = NamingStrategyFactory::create($naming);

        $hasValue = false;
        $id = null;

        foreach ($meta->properties as $property) {
            foreach ($this->adapters as $adapter) {
                if ($adapter->supports($property)) {
                    continue 2;
                }
            }

            $column = $this->getColumn($property, $namer, $prefix);

            if (!\array_key_exists($column, $row)) {
                continue;
            }

            $value = $row[$column];

            if ($value !== null) {
                $hasValue = true;
            }

            if (!empty($property->getAttributes(Identifier::class))) {
                $id = (string)$value;
            }

            $value = $this->transformerValue($property, $value);

            $property->setValue($dto, $value);
        }

        if (!$hasValue || $id === null) {
            return null;
        }

        if ($this->identityMap->has($dtoClass, $id)) {
            return $this->identityMap->get($dtoClass, $id);
        }

        $this->identityMap->put($dtoClass, $id, $dto);

        return $dto;
    }

    public function hydrateNested(array $row, object $dto): void
    {
        $meta = MetadataFactory::get($dto::class);

        foreach ($meta->properties as $property) {
            foreach ($this->adapters as $adapter) {
                if ($adapter->supports($property)) {
                    $adapter->map($property, $row, $dto, $this);
                }
            }
        }
    }

    public function extractIdentifier(object $dto): string
    {
        $meta = MetadataFactory::get($dto::class);

        foreach ($meta->properties as $property) {
            if (!empty($property->getAttributes(Identifier::class))) {
                return (string)$property->getValue($dto);
            }
        }

        throw new \RuntimeException('Identifier not found');
    }

    private function getColumn(\ReflectionProperty $property, NamingStrategyInterface $namer, string $prefix): string
    {
        $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
        $columnName = $columnAttr
            ? $columnAttr->newInstance()->name
            : $namer->convert($property->getName());

        return \sprintf('%s%s', $prefix, $columnName);
    }

    private function transformerValue(\ReflectionProperty $property, mixed $value): mixed
    {
        $transformerAttr = $property->getAttributes(TransformerAttribute::class)[0] ?? null;

        if ($transformerAttr) {
            $transformerName = $transformerAttr->newInstance()->name;
            $value = $this->transformer->transform($value, $transformerName);
        }

        return $value;
    }
}
