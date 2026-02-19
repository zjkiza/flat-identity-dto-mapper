<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Strategy;

use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Enum\Naming;

final class NamingStrategyFactory
{
    public static function create(Naming $type): NamingStrategyInterface
    {
        return match ($type) {
            Naming::SnakeToCamel => new SnakeToCamelStrategy(),
            Naming::CamelToSnake => new CamelToSnakeStrategy(),
        };
    }
}
