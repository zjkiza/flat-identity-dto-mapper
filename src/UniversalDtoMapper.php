<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper;

use ZJKiza\FlatMapper\Adapter\CollectionAdapter;
use ZJKiza\FlatMapper\Adapter\ObjectAdapter;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;
use ZJKiza\FlatMapper\Contract\TransformerInterface;
use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Contract\UniversalDtoMapperInterface;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Exception\InvalidArrayKayException;
use ZJKiza\FlatMapper\Exception\InvalidAttributeException;
use ZJKiza\FlatMapper\Identity\IdentityMap;
use ZJKiza\FlatMapper\Metadata\DtoMetadata;
use ZJKiza\FlatMapper\Metadata\MetadataFactory;
use ZJKiza\FlatMapper\Strategy\NamingStrategyFactory;
use ZJKiza\FlatMapper\Transformer\Transformer;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class UniversalDtoMapper implements UniversalDtoMapperInterface
{
    /** @var array<string, AttributeAdapterInterface>*/
    private array $adapters;

    private IdentityMap $identityMap;

    public function __construct(
        private readonly TransformerInterface $transformer = new Transformer()
    ) {
        $this->addAdapter(new ObjectAdapter());
        $this->addAdapter(new CollectionAdapter());
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
        $dtoMetadata = MetadataFactory::get($dtoClass);

        InvalidAttributeException::throwIf(
            condition: false === $dtoMetadata->hasColumnPrefix(),
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
             */
            $grouped[$row[$rootId]][] = $row;
        }

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
        $dtoMetadata = MetadataFactory::get($dtoClass);
        $dto = $dtoMetadata->reflectionClass->newInstanceWithoutConstructor();

        $prefix = $forcedPrefix ?? ($dtoMetadata->getColumnPrefix()?->name ?? '');
        $naming = $forcedNaming ?? ($dtoMetadata->getColumnPrefix()->naming ?? Naming::CamelToSnake);
        $namer = NamingStrategyFactory::create($naming);

        $hasValue = false;
        $id = null;

        foreach ($dtoMetadata->properties as $property) {

            // Direct access instead of foreach 13% speedup (replaced in 2 places)
            $attrs = $property->getAttributes();
            if ($attrs && isset($this->adapters[$attrs[0]->getName()])) {
                break;
            }

            if ($dtoMetadata->hasIgnore($property->getName())) {
                continue;
            }

            $column = $this->getColumn($property, $namer, $prefix, $dtoMetadata);

            if (!\array_key_exists($column, $row)) {
                continue;
            }

            $value = $row[$column];

            if (null !== $value) {
                $hasValue = true;
            }

            if ($dtoMetadata->hasIdentifier($property->getName())) {
                $id = (string)$value;
            }

            $value = $this->transformerValue($property, $value, $dtoMetadata);

            $property->setValue($dto, $value);
        }

        if (!$hasValue || null === $id) {
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

            $attrs = $property->getAttributes();
            if ($attrs && isset($this->adapters[$attrs[0]->getName()])) {
                $this->adapters[$attrs[0]->getName()]->map($property, $row, $dto, $this);
            }
        }
    }

    public function extractIdentifier(object $dto): string
    {
        $meta = MetadataFactory::get($dto::class);

        foreach ($meta->properties as $property) {
            if ((bool)$property->getAttributes(Identifier::class)) {
                /** @phpstan-ignore-next-line  */
                return (string)$property->getValue($dto);
            }
        }

        throw new InvalidAttributeException(\sprintf('Attribute Identifier not found in Dto class %s', $dto::class));
    }

    /**
     * @param DtoMetadata<object> $metadata
     */
    private function getColumn(\ReflectionProperty $property, NamingStrategyInterface $namer, string $prefix, DtoMetadata $metadata): string
    {
        $propertyName = $property->getName();

        if ($metadata->hasColumnAttribute($propertyName)) {
            $columnName = $metadata->getColumnNameFromAttribute($propertyName);
        } else {
            $columnName = $namer->convert($propertyName);
        }

        return $prefix . $columnName;
    }

    /**
     * @param DtoMetadata<object> $metadata
     */
    private function transformerValue(\ReflectionProperty $property, mixed $value, DtoMetadata $metadata): mixed
    {
        $propertyName = $property->getName();

        $transformerName = $metadata->getTransformerName($propertyName);

        if (null !== $transformerName) {
            return $this->transformer->transform($value, $transformerName);
        }

        return $value;
    }

    private function addAdapter(AttributeAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->indexKay()] = $adapter;
    }
}
