<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Adapter;

use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Contract\AttributeAdapterInterface;

final class ObjectAdapter implements AttributeAdapterInterface
{
    public function supports(\ReflectionProperty $property): bool
    {
        return !empty($property->getAttributes(ObjectDto::class));
    }

    public function map(
        \ReflectionProperty $property,
        array $row,
        object $dto,
        UniversalDtoMapper $mapper
    ): void {

        $attr = $property->getAttributes(ObjectDto::class)[0]->newInstance();

        $object = $mapper->hydrateScalar(
            $row,
            $attr->className,
            $attr->columnPrefix,
            $attr->naming
        );

        /**
         * KLJUČNA ZAŠTITA:
         * Ne prepisuj već mapiran objekat ako novi mapping vrati null
         */
        if ($object !== null) {
            $mapper->hydrateNested($row, $object);
            $property->setValue($dto, $object);
        }
    }
}
