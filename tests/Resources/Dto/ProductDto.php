<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\Column;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Ignore;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Enum\Naming;

#[ColumnPrefix(name: 'product_', naming: Naming::CamelToSnake)]
final class ProductDto
{
    #[Identifier]
    public ?string $id = null;

    public ?string $name = null;

    /** Property name does not match the column ("product_full_title"). */
    #[Column('full_title')]
    public ?string $title = null;

    /** camelCase property, snake_case column ("product_created_at"). */
    public ?string $createdAt = null;

    /** The column "product_secret" exists in the rows but must never be hydrated. */
    #[Ignore]
    public ?string $secret = 'untouched';

    #[ObjectDto(className: CategoryDto::class, columnPrefix: 'category_', naming: Naming::CamelToSnake)]
    public ?CategoryDto $category = null;

    #[Collection(className: ReviewDto::class, columnPrefix: 'review_', naming: Naming::CamelToSnake)]
    public iterable|null $reviews = null;
}
