<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Enum\Naming;

/**
 * Properties are written in snake_case, the columns are camelCase ("user_firstName").
 */
#[ColumnPrefix(name: 'user_', naming: Naming::SnakeToCamel)]
final class SnakeCasePropertyDto
{
    #[Identifier]
    public ?string $id = null;

    public ?string $first_name = null;
}
