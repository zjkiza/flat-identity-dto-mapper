<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Strategy\CamelToSnakeStrategy;
use ZJKiza\FlatMapper\Strategy\NamingStrategyFactory;
use ZJKiza\FlatMapper\Strategy\SnakeToCamelStrategy;

final class NamingStrategyFactoryTest extends TestCase
{
    /**
     * @param class-string $expectedClass
     */
    #[DataProvider('provideNamings')]
    public function testCreate(Naming $naming, string $expectedClass): void
    {
        $this->assertInstanceOf($expectedClass, NamingStrategyFactory::create($naming));
    }

    /**
     * @return iterable<string, array{Naming, class-string}>
     */
    public static function provideNamings(): iterable
    {
        yield 'camel to snake' => [Naming::CamelToSnake, CamelToSnakeStrategy::class];
        yield 'snake to camel' => [Naming::SnakeToCamel, SnakeToCamelStrategy::class];
    }
}
