<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Collection;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Collection\LazyCollection;

final class LazyCollectionTest extends TestCase
{
    public function testInitializerIsNotCalledUntilNeeded(): void
    {
        $calls = 0;
        $collection = new LazyCollection(static function () use (&$calls): array {
            ++$calls;

            return ['a' => 1];
        });

        $this->assertFalse($collection->isInitialized());
        $this->assertSame(0, $calls);
    }

    public function testInitializerIsCalledOnlyOnceAcrossAllAccessors(): void
    {
        $calls = 0;
        $collection = new LazyCollection(static function () use (&$calls): array {
            ++$calls;

            return ['a' => 1, 'b' => 2];
        });

        $this->assertCount(2, $collection);
        $this->assertSame(['a' => 1, 'b' => 2], $collection->toArray());
        $this->assertSame(['a' => 1, 'b' => 2], \iterator_to_array($collection));
        $this->assertTrue($collection->isInitialized());
        $this->assertSame(1, $calls);
    }

    public function testGetIteratorPreservesKeysAndOrder(): void
    {
        $collection = new LazyCollection(static fn (): array => ['x' => 'first', 'y' => 'second']);

        $this->assertSame(['x', 'y'], \array_keys(\iterator_to_array($collection->getIterator())));
        $this->assertTrue($collection->isInitialized());
    }

    public function testEmptyCollection(): void
    {
        $collection = new LazyCollection(static fn (): array => []);

        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->toArray());
    }
}
