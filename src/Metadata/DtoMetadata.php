<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Metadata;

use ZJKiza\FlatMapper\Attribute\Column;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Ignore;
use ZJKiza\FlatMapper\Attribute\Transformer;

/**
 * @template T of object
 */
final class DtoMetadata
{
    /** @var array<string, string|null>*/
    private array $columnAttributeNames;

    /** @var array<string, bool> */
    private array $ignoreAttributes;

    /** @var array<string, bool> */
    private array $identifierAttributes;

    /** @var array<string, string|null> */
    private array $transformerAttributes;

    /**
     * @param \ReflectionClass<T> $reflectionClass
     * @param \ReflectionProperty[] $properties
     */
    public function __construct(
        public \ReflectionClass $reflectionClass,
        /** @var \ReflectionProperty[] */
        public array            $properties
    ) {
        // Adding caching attribute get acceleration 2.4 %
        foreach ($properties as $property) {
            $name = $property->getName();

            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            $this->columnAttributeNames[$name] = $columnAttr?->newInstance()->name;

            $this->ignoreAttributes[$name] = (bool)$property->getAttributes(Ignore::class);

            $this->identifierAttributes[$name] = (bool)$property->getAttributes(Identifier::class);

            $transformerAttr = $property->getAttributes(Transformer::class)[0] ?? null;
            $this->transformerAttributes[$name] = $transformerAttr?->newInstance()->name;
        }
    }

    public function hasColumnAttribute(string $propertyName): bool
    {
        return isset($this->columnAttributeNames[$propertyName]);
    }

    public function getColumnNameFromAttribute(string $propertyName): ?string
    {
        return $this->columnAttributeNames[$propertyName] ?? null;
    }

    public function hasIgnore(string $propertyName): bool
    {
        return $this->ignoreAttributes[$propertyName] ?? false;
    }

    public function hasIdentifier(string $propertyName): bool
    {
        return $this->identifierAttributes[$propertyName] ?? false;
    }

    public function getTransformerName(string $propertyName): ?string
    {
        return $this->transformerAttributes[$propertyName] ?? null;
    }
}
