<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Transformer;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Tests\Resources\Transformer\TrimTransformer;

#[ColumnPrefix(name: 'product_', naming: Naming::CamelToSnake)]
final class TrimmedProductDto
{
    #[Identifier]
    public ?string $id = null;

    #[Transformer(TrimTransformer::class)]
    public ?string $name = null;
}
