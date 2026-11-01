<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Enum\Naming;

#[ColumnPrefix(name: 'item_', naming: Naming::CamelToSnake)]
final class NoIdentifierDto
{
    public ?string $id = null;

    public ?string $name = null;
}
