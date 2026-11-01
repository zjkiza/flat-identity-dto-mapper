<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Transformer;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Exception\InvalidObjectInstanceException;
use ZJKiza\FlatMapper\Tests\Resources\Transformer\TrimTransformer;
use ZJKiza\FlatMapper\Transformer\Transformer;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

final class TransformerTest extends TestCase
{
    public function testUpperTransformerIsRegisteredByDefault(): void
    {
        $this->assertSame('ABC', (new Transformer())->transform('abc', UpperTransformer::class));
    }

    public function testTransformersPassedToConstructorAreRegistered(): void
    {
        $transformer = new Transformer([new TrimTransformer()]);

        $this->assertSame('abc', $transformer->transform('  abc  ', TrimTransformer::class));
        $this->assertSame('ABC', $transformer->transform('abc', UpperTransformer::class));
    }

    public function testAddTransformerRegistersItByClassName(): void
    {
        $transformer = new Transformer();
        $transformer->addTransformer(new TrimTransformer());

        $this->assertSame('abc', $transformer->transform('  abc  ', TrimTransformer::class));
    }

    public function testUnknownTransformerThrows(): void
    {
        $this->expectException(InvalidObjectInstanceException::class);
        $this->expectExceptionMessage(\sprintf(
            'Object with instance "%s" does not exist! Check that you have imported that instance into a Mapper class constructor.',
            TrimTransformer::class
        ));

        (new Transformer())->transform('abc', TrimTransformer::class);
    }
}
