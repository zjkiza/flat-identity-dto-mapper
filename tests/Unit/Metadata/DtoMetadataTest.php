<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Unit\Metadata;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Metadata\MetadataFactory;
use ZJKiza\FlatMapper\Tests\Resources\Dto\CategoryDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ProductDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\TrimmedProductDto;
use ZJKiza\FlatMapper\Tests\Resources\Transformer\TrimTransformer;

final class DtoMetadataTest extends TestCase
{
    public function testColumnPrefix(): void
    {
        $metadata = MetadataFactory::get(ProductDto::class);

        $prefix = $metadata->getColumnPrefix();

        $this->assertTrue($metadata->hasColumnPrefix());
        $this->assertNotNull($prefix);
        $this->assertSame('product_', $prefix->name);
        $this->assertSame(Naming::CamelToSnake, $prefix->naming);
    }

    public function testClassWithoutColumnPrefix(): void
    {
        $metadata = MetadataFactory::get(CategoryDto::class);

        $this->assertFalse($metadata->hasColumnPrefix());
        $this->assertNull($metadata->getColumnPrefix());
    }

    public function testColumnAttribute(): void
    {
        $metadata = MetadataFactory::get(ProductDto::class);

        $this->assertTrue($metadata->hasColumnAttribute('title'));
        $this->assertSame('full_title', $metadata->getColumnNameFromAttribute('title'));
        $this->assertFalse($metadata->hasColumnAttribute('name'));
        $this->assertNull($metadata->getColumnNameFromAttribute('name'));
        $this->assertFalse($metadata->hasColumnAttribute('unknown'));
    }

    public function testIgnoreAttribute(): void
    {
        $metadata = MetadataFactory::get(ProductDto::class);

        $this->assertTrue($metadata->hasIgnore('secret'));
        $this->assertFalse($metadata->hasIgnore('name'));
        $this->assertFalse($metadata->hasIgnore('unknown'));
    }

    public function testIdentifierAttribute(): void
    {
        $metadata = MetadataFactory::get(ProductDto::class);

        $this->assertTrue($metadata->hasIdentifier('id'));
        $this->assertFalse($metadata->hasIdentifier('name'));
        $this->assertFalse($metadata->hasIdentifier('unknown'));
    }

    public function testTransformerAttribute(): void
    {
        $this->assertSame(TrimTransformer::class, MetadataFactory::get(TrimmedProductDto::class)->getTransformerName('name'));
        $this->assertNull(MetadataFactory::get(TrimmedProductDto::class)->getTransformerName('id'));
        $this->assertNull(MetadataFactory::get(ProductDto::class)->getTransformerName('name'));
        $this->assertNull(MetadataFactory::get(ProductDto::class)->getTransformerName('unknown'));
    }
}
