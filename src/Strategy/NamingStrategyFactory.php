<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Strategy;

use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;

final class NamingStrategyFactory
{
    public static function create(string $type): NamingStrategyInterface
    {
        return match ($type) {
            'snakeToCamel' => new SnakeToCamelStrategy(),
            default => throw new \InvalidArgumentException("Unknown naming strategy $type")
        };
    }
}
