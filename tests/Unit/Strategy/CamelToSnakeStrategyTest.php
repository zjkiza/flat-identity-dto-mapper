<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Strategy\CamelToSnakeStrategy;

final class CamelToSnakeStrategyTest extends TestCase
{
    #[DataProvider('provideNames')]
    public function testConvert(string $input, string $expected): void
    {
        $this->assertSame($expected, (new CamelToSnakeStrategy())->convert($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideNames(): iterable
    {
        yield 'single word' => ['id', 'id'];
        yield 'camelCase' => ['firstName', 'first_name'];
        yield 'PascalCase' => ['Name', 'name'];
        yield 'trailing acronym' => ['userID', 'user_id'];
        yield 'leading acronym' => ['HTMLParser', 'html_parser'];
        yield 'only acronym' => ['URL', 'url'];
        yield 'digits stay attached' => ['withNumber2', 'with_number2'];
        yield 'already snake_case' => ['already_snake', 'already_snake'];
        yield 'unicode' => ['ÉcoleName', 'école_name'];
        yield 'empty string' => ['', ''];
    }
}
