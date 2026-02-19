<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Strategy;

use ZJKiza\FlatMapper\Contract\NamingStrategyInterface;
use ZJKiza\FlatMapper\Exception\InvalidArgumentException;

final class CamelToSnakeStrategy implements NamingStrategyInterface
{
    public function convert(string $column): string
    {
        $snake = \preg_replace(
            '/(?<!^)(?<!_)((?=[\p{Lu}][^\p{Lu}])|(?<![\p{Lu}])(?=[\p{Lu}]))/u',
            '_',
            $column
        );

        if (null === $snake) {
            throw new InvalidArgumentException('Failed to convert camelCase to snake_case.');
        }

        return \mb_strtolower($snake);
    }
}
