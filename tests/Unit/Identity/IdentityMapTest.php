<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Identity;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Identity\IdentityMap;

final class IdentityMapTest extends TestCase
{
    public function testEmptyMapHasNothing(): void
    {
        $map = new IdentityMap();

        $this->assertSame(0, $map->count());
        $this->assertFalse($map->has(\stdClass::class, '1'));
    }

    public function testPutAndGetReturnTheSameInstance(): void
    {
        $map = new IdentityMap();
        $object = new \stdClass();

        $map->put(\stdClass::class, '1', $object);

        $this->assertTrue($map->has(\stdClass::class, '1'));
        $this->assertSame($object, $map->get(\stdClass::class, '1'));
        $this->assertSame(1, $map->count());
    }

    public function testSameIdInDifferentClassesDoesNotCollide(): void
    {
        $map = new IdentityMap();
        $first = new \stdClass();
        $second = new \ArrayObject();

        $map->put(\stdClass::class, '1', $first);
        $map->put(\ArrayObject::class, '1', $second);

        $this->assertSame($first, $map->get(\stdClass::class, '1'));
        $this->assertSame($second, $map->get(\ArrayObject::class, '1'));
        $this->assertSame(2, $map->count());
    }

    public function testPutOverwritesExistingEntry(): void
    {
        $map = new IdentityMap();
        $replacement = new \stdClass();

        $map->put(\stdClass::class, '1', new \stdClass());
        $map->put(\stdClass::class, '1', $replacement);

        $this->assertSame($replacement, $map->get(\stdClass::class, '1'));
        $this->assertSame(1, $map->count());
    }
}
