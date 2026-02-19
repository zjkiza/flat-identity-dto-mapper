<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Metadata;

/**
 * @psalm-suppress DocblockTypeContradiction
 */
final class MetadataFactory
{
    /**
     * @var array<class-string, DtoMetadata<object>>
     */
    private static array $cache = [];

    /**
     * @template T of object
     *
     * @param T|class-string<T> $dtoClass
     *
     * @return DtoMetadata<T>
     */
    public static function get(object|string $dtoClass): DtoMetadata
    {
        /** @var class-string<T> $stringDtoClass */
        $stringDtoClass = \is_string($dtoClass) ? $dtoClass : $dtoClass::class;

        if (!isset(self::$cache[$stringDtoClass])) {
            /** @var \ReflectionClass<T> $reflectionClass */
            $reflectionClass = new \ReflectionClass($stringDtoClass);

            self::$cache[$stringDtoClass] = new DtoMetadata(
                $reflectionClass,
                $reflectionClass->getProperties()
            );
        }

        /** @var DtoMetadata<T> */
        return self::$cache[$stringDtoClass];
    }
}
