<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Enum\Naming;

#[ColumnPrefix(name: 'product_', naming: Naming::CamelToSnake)]
final class ProductLazyDto
{
    #[Identifier]
    public ?string $id = null;

    public ?string $name = null;

    #[Collection(className: ReviewDto::class, columnPrefix: 'review_', naming: Naming::CamelToSnake, lazy: true)]
    public iterable|null $reviews = null;

    #[Collection(className: TagDto::class, columnPrefix: 'tag_', naming: Naming::CamelToSnake, lazy: true)]
    public iterable|null $tags = null;
}
