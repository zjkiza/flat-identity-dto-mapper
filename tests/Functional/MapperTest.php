<?php

declare(strict_types=1);

namespace Functional;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto;

final class MapperTest extends TestCase
{
    private UniversalDtoMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new UniversalDtoMapper();
    }

    public function testSuccess(): void
    {
        $data = __DIR__.'/../Resources/Data/data.json';
        $jsonStringData = \file_get_contents($data);
        $inputData = \json_decode($jsonStringData, true);

        $expected = __DIR__.'/../Resources/Data/expected.json';
        $jsonStringExpected = \file_get_contents($expected);

        $dto = $this->mapper->map($inputData, MediaDto::class, 'media_id');

        $json = \json_encode($dto, JSON_THROW_ON_ERROR);

        $this->assertSame($jsonStringExpected, $json);
    }
}
