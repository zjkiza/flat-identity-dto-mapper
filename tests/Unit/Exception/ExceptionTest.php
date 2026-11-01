<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Contract\ExceptionInterface;
use ZJKiza\FlatMapper\Exception\InvalidArgumentException;
use ZJKiza\FlatMapper\Exception\InvalidArrayKayException;
use ZJKiza\FlatMapper\Exception\InvalidAttributeException;
use ZJKiza\FlatMapper\Exception\InvalidObjectInstanceException;
use ZJKiza\FlatMapper\Exception\TransformationFailedException;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

final class ExceptionTest extends TestCase
{
    /**
     * @param class-string<\Throwable> $class
     */
    #[DataProvider('provideExceptionClasses')]
    public function testAllExceptionsImplementLibraryInterface(string $class): void
    {
        $this->assertInstanceOf(ExceptionInterface::class, new $class('message'));
    }

    /**
     * @return iterable<string, array{class-string<\Throwable>}>
     */
    public static function provideExceptionClasses(): iterable
    {
        yield 'invalid argument' => [InvalidArgumentException::class];
        yield 'invalid array key' => [InvalidArrayKayException::class];
        yield 'invalid attribute' => [InvalidAttributeException::class];
        yield 'invalid object instance' => [InvalidObjectInstanceException::class];
        yield 'transformation failed' => [TransformationFailedException::class];
    }

    public function testThrowIfDoesNothingWhenConditionIsFalse(): void
    {
        $this->expectNotToPerformAssertions();

        InvalidAttributeException::throwIf(false, 'not thrown');
        InvalidArrayKayException::throwIf(false, 'not thrown');
    }

    public function testThrowIfThrowsSelfWithMessageWhenConditionIsTrue(): void
    {
        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionMessage('boom');

        InvalidAttributeException::throwIf(true, 'boom');
    }

    public function testThrowIfTreatsAnyTruthyValueAsTrue(): void
    {
        $this->expectException(InvalidArrayKayException::class);

        InvalidArrayKayException::throwIf('yes', 'boom');
    }

    public function testTransformationFailedExceptionKeepsPreviousException(): void
    {
        $previous = new \LogicException('cause');

        $exception = TransformationFailedException::create(5, new UpperTransformer(), $previous);

        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('Unable to transform value of type "int" using "' . UpperTransformer::class . '" transformer.', $exception->getMessage());
    }
}
