<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Exception\TransformationFailedException;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

final class UpperTransformerTest extends TestCase
{
    public function testNullStaysNull(): void
    {
        $this->assertNull((new UpperTransformer())->transform(null));
    }

    public function testStringIsUpperCased(): void
    {
        $this->assertSame('TITLE VIDEO 9', (new UpperTransformer())->transform('Title video 9'));
    }

    public function testEmptyStringStaysEmpty(): void
    {
        $this->assertSame('', (new UpperTransformer())->transform(''));
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidTypeThrows(mixed $value, string $expectedType): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage(\sprintf(
            'Unable to transform value of type "%s" using "%s" transformer.',
            $expectedType,
            UpperTransformer::class
        ));

        (new UpperTransformer())->transform($value);
    }

    /**
     * @return iterable<string, array{mixed, string}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'int' => [5, 'int'];
        yield 'float' => [1.5, 'float'];
        yield 'bool' => [true, 'bool'];
        yield 'array' => [['a'], 'array'];
        yield 'object' => [new \stdClass(), 'stdClass'];
    }
}
