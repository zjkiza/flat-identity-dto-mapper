<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Strategy;

use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;

final class SnakeToCamelStrategy implements NamingStrategyInterface
{
    public function convert(string $column): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($column, '_')));
    }
}
