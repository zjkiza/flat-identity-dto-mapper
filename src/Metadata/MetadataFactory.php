<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Metadata;

final class MetadataFactory
{
    /** @var array<class-string, DtoMetadata> */
    private static array $cache = [];

    public static function get(object|string $dtoClass): DtoMetadata
    {
        $stringDtoClass = \is_string($dtoClass) ? $dtoClass : $dtoClass::class;

        if (!isset(self::$cache[$stringDtoClass])) {
            $reflectionClass = new \ReflectionClass($stringDtoClass);

            self::$cache[$stringDtoClass] = new DtoMetadata(
                $reflectionClass,
                $reflectionClass->getProperties()
            );
        }

        return self::$cache[$stringDtoClass];
    }
}
