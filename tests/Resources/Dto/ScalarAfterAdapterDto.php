<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Enum\Naming;

/**
 * Documents the invariant: scalar properties must be declared BEFORE the first #[ObjectDto]/#[Collection]
 * property, because scalar hydration stops at the first adapter property.
 */
#[ColumnPrefix(name: 'product_', naming: Naming::CamelToSnake)]
final class ScalarAfterAdapterDto
{
    #[Identifier]
    public ?string $id = null;

    #[ObjectDto(className: CategoryDto::class, columnPrefix: 'category_', naming: Naming::CamelToSnake)]
    public ?CategoryDto $category = null;

    public ?string $name = null;
}
