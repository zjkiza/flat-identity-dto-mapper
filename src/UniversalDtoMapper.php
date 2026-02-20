<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper;

use ZJKiza\FlatMapper\Adapter\CollectionAdapter;
use ZJKiza\FlatMapper\Adapter\ObjectAdapter;
use ZJKiza\FlatMapper\Attribute\Column;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Ignore;
use ZJKiza\FlatMapper\Attribute\Transformer as TransformerAttribute;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;
use ZJKiza\FlatMapper\Contract\TransformerInterface;
use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Contract\UniversalDtoMapperInterface;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Exception\InvalidArrayKayException;
use ZJKiza\FlatMapper\Exception\InvalidAttributeException;
use ZJKiza\FlatMapper\Identity\IdentityMap;
use ZJKiza\FlatMapper\Metadata\MetadataFactory;
use ZJKiza\FlatMapper\Strategy\NamingStrategyFactory;
use ZJKiza\FlatMapper\Transformer\Transformer;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class UniversalDtoMapper implements UniversalDtoMapperInterface
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

    /**
     * @template T of object
     *
     * @param array<int, array<string, scalar|null>> $rows
     * @param class-string<T> $dtoClass
     *
     * @return T[]
     */
    public function map(array $rows, string $dtoClass, string $rootId): array
    {
        $meta = MetadataFactory::get($dtoClass);
        $dtoColumnPrefix = $meta->reflectionClass->getAttributes(ColumnPrefix::class)[0] ?? null;

        InvalidAttributeException::throwIf(
            condition: null === $dtoColumnPrefix,
            message: \sprintf('The mandatory "ZJKiza\FlatMapper\Attribute\ColumnPrefix" attribute is not defined on the Dto class "%s"', $dtoClass)
        );

        $this->identityMap = new IdentityMap();

        $grouped = [];

        foreach ($rows as $row) {

            InvalidArrayKayException::throwIf(
                condition: !isset($row[$rootId]),
                message: \sprintf('Root ID "%s" is not exist in input array or cannot be null in row: %s', $rootId, \json_encode($row, JSON_THROW_ON_ERROR))
            );

            /**
             * @psalm-suppress PossiblyNullArrayOffset
             * @phpstan-ignore-next-line
             */
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

                InvalidArrayKayException::throwIf(
                    condition: null === $dto,
                    message: \sprintf('In column row "%s" there is no key name defined for scalar value in DTO class "%s".', \json_encode($row, JSON_THROW_ON_ERROR), $dtoClass)
                );

                /**
                 * @psalm-suppress PossiblyNullArgument
                 * @phpstan-ignore-next-line
                 */
                $this->hydrateNested($row, $dto);
            }

            $result[] = $dto;
        }

        /** @var T[] */
        return $result;
    }

    /**
     * @param array<string, scalar|null> $row
     * @param class-string $dtoClass
     */
    public function hydrateScalar(
        array   $row,
        string  $dtoClass,
        ?string $forcedPrefix = null,
        ?Naming $forcedNaming = null
    ): ?object {
        $meta = MetadataFactory::get($dtoClass);
        $dto = $meta->reflectionClass->newInstanceWithoutConstructor();

        $classPrefixAttr = $meta->reflectionClass->getAttributes(ColumnPrefix::class)[0] ?? null;
        $classPrefix = $classPrefixAttr?->newInstance();

        $prefix = $forcedPrefix ?? ($classPrefix->name ?? '');
        $naming = $forcedNaming ?? ($classPrefix->naming ?? Naming::CamelToSnake);
        $namer = NamingStrategyFactory::create($naming);

        $hasValue = false;
        $id = null;

        foreach ($meta->properties as $property) {
            foreach ($this->adapters as $adapter) {
                if ($adapter->supports($property)) {
                    continue 2;
                }
            }

            if (!empty($property->getAttributes(Ignore::class))) {
                continue;
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

    /**
     * @param array<string, scalar|null> $row
     */
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
                /** @phpstan-ignore-next-line  */
                return (string)$property->getValue($dto);
            }
        }

        throw new InvalidAttributeException(\sprintf('Attribute Identifier not found in Dto class %s', $dto::class));
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
