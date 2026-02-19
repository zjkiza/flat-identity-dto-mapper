<?php

declare(strict_types=1);

namespace Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Exception\InvalidArrayKayException;
use ZJKiza\FlatMapper\Exception\InvalidAttributeException;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaLazyDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\TagDto;
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto;

final class MapperTest extends TestCase
{
    private UniversalDtoMapper $mapper;

    /** @var array<int, array<string, scalar|null>> */
    private array $inputData;
    private string $expected;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new UniversalDtoMapper();

        $expected = __DIR__ . '/../Resources/Data/expected.json';
        $expectedContent = \file_get_contents($expected);
        \assert(\is_string($expectedContent));
        $this->expected = $expectedContent;

        $data = __DIR__ . '/../Resources/Data/data.json';
        $jsonStringData = \file_get_contents($data);
        \assert(\is_string($jsonStringData));

        /** @var array<int, array<string, scalar|null>>  $inputData */
        $inputData = \json_decode($jsonStringData, true);
        $this->inputData = $inputData;
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('getDataForSuccess')]
    public function testSuccess(string $className): void
    {
        $dto = $this->mapper->map($this->inputData, $className, 'media_id');
        $json = \json_encode($dto, JSON_THROW_ON_ERROR);
        $this->assertSame($this->expected, $json);
    }

    /**
     * @return iterable<string, array{0: class-string}>
     */
    public static function getDataForSuccess(): iterable
    {
        yield 'Success without lazy collection' => [
            MediaDto::class,
        ];

        yield 'Success with lazy collection' => [
            MediaLazyDto::class
        ];
    }

    public function testSuccessWithLazy(): void
    {
        $expected = __DIR__ . '/../Resources/Data/expected.json';
        $jsonStringExpected = \file_get_contents($expected);

        $dto = $this->mapper->map($this->inputData, MediaDto::class, 'media_id');

        $json = \json_encode($dto, JSON_THROW_ON_ERROR);

        $this->assertSame($jsonStringExpected, $json);
    }

    public function testExpectExceptionWhenRootIdIsNotExist(): void
    {
        $this->expectException(InvalidArrayKayException::class);
        $this->expectExceptionMessage('Root ID "media_id" is not exist in input array or cannot be null in row: {"media_test":"test"}');

        $input = [
            ['media_test' => 'test'],
        ];

        $this->mapper->map($input, MediaDto::class, 'media_id');
    }

    public function testExpectExceptionWhenAttributeColumnPrefixNotExist(): void
    {
        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionMessage('The mandatory "ZJKiza\FlatMapper\Attribute\ColumnPrefix" attribute is not defined on the Dto class "ZJKiza\FlatMapper\Tests\Resources\Dto\TagDto"');

        $input = [
            ['media_test' => 'test'],
        ];

        $this->mapper->map($input, TagDto::class, 'media_test');
    }

    public function testExpectExceptionWhenKeyNotExistInRow(): void
    {
        $this->expectException(InvalidArrayKayException::class);
        $this->expectExceptionMessage('In column row "{"media_test":"test"}" there is no key name defined for scalar value in DTO class "ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto".');

        $input = [
            ['media_test' => 'test'],
        ];

        $this->mapper->map($input, MediaDto::class, 'media_test');
    }
}
