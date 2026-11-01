<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Strategy\SnakeToCamelStrategy;

final class SnakeToCamelStrategyTest extends TestCase
{
    #[DataProvider('provideNames')]
    public function testConvert(string $input, string $expected): void
    {
        $this->assertSame($expected, (new SnakeToCamelStrategy())->convert($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideNames(): iterable
    {
        yield 'single word' => ['id', 'id'];
        yield 'snake_case' => ['first_name', 'firstName'];
        yield 'many parts' => ['a_b_c', 'aBC'];
        yield 'upper case input is lowered first' => ['FIRST_NAME', 'firstName'];
        yield 'unicode' => ['škola_ime', 'školaIme'];
        yield 'underscore followed by digit is kept' => ['user_2fa', 'user_2fa'];
        yield 'trailing underscore is kept' => ['trailing_', 'trailing_'];
        yield 'leading underscore' => ['_leading', 'Leading'];
        yield 'double underscore' => ['double__under', 'double_Under'];
        yield 'empty string' => ['', ''];
    }
}
