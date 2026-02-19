<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Enum;

enum Naming: string
{
    case SnakeToCamel = 'snakeToCamel';

    case CamelToSnake = 'camelToSnake';
}
