<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Strategy;

use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Exception\InvalidArgumentException;

final class SnakeToCamelStrategy implements NamingStrategyInterface
{
    public function convert(string $column): string
    {
        $string = \mb_strtolower($column);

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $camel = \preg_replace_callback(
            '/_([\p{L}])/u',
            static fn ($matches) => \mb_strtoupper((string) $matches[1]),
            $string
        );

        if (null === $camel) {
            throw new InvalidArgumentException('Failed to convert snake_case to camelCase.');
        }

        return $camel;
    }
}
