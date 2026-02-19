<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Metadata;

/**
 * @template T of object
 */
final readonly class DtoMetadata
{
    /**
     * @param \ReflectionClass<T> $reflectionClass
     * @param \ReflectionProperty[] $properties
     */
    public function __construct(
        public \ReflectionClass $reflectionClass,
        /** @var \ReflectionProperty[] */
        public array            $properties
    ) {
    }
}
