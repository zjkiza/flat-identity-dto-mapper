<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Metadata;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Metadata\MetadataFactory;
use ZJKiza\FlatMapper\Tests\Resources\Dto\CategoryDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ProductDto;

final class MetadataFactoryTest extends TestCase
{
    public function testMetadataIsCachedPerClass(): void
    {
        $this->assertSame(MetadataFactory::get(ProductDto::class), MetadataFactory::get(ProductDto::class));
    }

    public function testClassNameAndInstanceResolveToTheSameMetadata(): void
    {
        $this->assertSame(MetadataFactory::get(ProductDto::class), MetadataFactory::get(new ProductDto()));
    }

    public function testDifferentClassesGetDifferentMetadata(): void
    {
        $this->assertNotSame(MetadataFactory::get(ProductDto::class), MetadataFactory::get(CategoryDto::class));
    }

    public function testPropertiesFollowDeclarationOrder(): void
    {
        $names = \array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            MetadataFactory::get(ProductDto::class)->properties
        );

        $this->assertSame(['id', 'name', 'title', 'createdAt', 'secret', 'category', 'reviews'], $names);
    }
}
