<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Functional;

use PHPUnit\Framework\TestCase;
use ZJKiza\FlatMapper\Collection\LazyCollection;
use ZJKiza\FlatMapper\Exception\InvalidArrayKayException;
use ZJKiza\FlatMapper\Exception\InvalidAttributeException;
use ZJKiza\FlatMapper\Exception\InvalidObjectInstanceException;
use ZJKiza\FlatMapper\Tests\Resources\Dto\NoIdentifierDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ProductDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ProductLazyDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ReviewDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\ScalarAfterAdapterDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\SnakeCasePropertyDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\TagDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\TrimmedProductDto;
use ZJKiza\FlatMapper\Tests\Resources\Transformer\TrimTransformer;
use ZJKiza\FlatMapper\Transformer\Transformer;
use ZJKiza\FlatMapper\UniversalDtoMapper;

/**
 * Behavior of the mapper on small, focused scenarios (see MapperTest for the big end-to-end fixture).
 */
final class MapperBehaviorTest extends TestCase
{
    private UniversalDtoMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new UniversalDtoMapper();
    }

    public function testMapsTraversableInput(): void
    {
        $rows = (static function (): \Generator {
            yield self::productRow();
            yield self::productRow(['review_id' => '101', 'review_text' => 'Good']);
        })();

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertCount(1, $result);
        $this->assertSame('1', $result[0]->id);
        $this->assertSame(['100', '101'], $this->ids($result[0]->reviews));
    }

    public function testMapsRowsWithIntegerValuesAsReturnedByDatabaseDrivers(): void
    {
        $rows = [
            self::productRow(['product_id' => 1, 'category_id' => 10, 'review_id' => 100]),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertSame('1', $result[0]->id);
        $this->assertNotNull($result[0]->category);
        $this->assertSame('10', $result[0]->category->id);
        $this->assertSame(['100'], $this->ids($result[0]->reviews));
    }

    public function testHydratesScalarsColumnAndCamelCaseProperties(): void
    {
        $result = $this->mapper->map([self::productRow()], ProductDto::class, 'product_id');

        $this->assertSame('Phone', $result[0]->name);
        $this->assertSame('Phone Pro', $result[0]->title, '#[Column] maps a differently named column.');
        $this->assertSame('2026-01-01', $result[0]->createdAt, 'camelCase property reads the snake_case column.');
    }

    public function testIgnoredPropertyIsNeverHydrated(): void
    {
        $result = $this->mapper->map([self::productRow(['product_secret' => 'leaked'])], ProductDto::class, 'product_id');

        $this->assertSame('untouched', $result[0]->secret);
    }

    public function testSnakeToCamelNamingStrategyReadsCamelCaseColumns(): void
    {
        $rows = [
            ['user_id' => '7', 'user_firstName' => 'Ana'],
        ];

        $result = $this->mapper->map($rows, SnakeCasePropertyDto::class, 'user_id');

        $this->assertSame('7', $result[0]->id);
        $this->assertSame('Ana', $result[0]->first_name);
    }

    public function testCustomTransformerRegisteredInConstructor(): void
    {
        $mapper = new UniversalDtoMapper(new Transformer([new TrimTransformer()]));

        $result = $mapper->map([['product_id' => '1', 'product_name' => '  Phone  ']], TrimmedProductDto::class, 'product_id');

        $this->assertSame('Phone', $result[0]->name);
    }

    public function testUnregisteredTransformerThrows(): void
    {
        $this->expectException(InvalidObjectInstanceException::class);
        $this->expectExceptionMessage(\sprintf('Object with instance "%s" does not exist!', TrimTransformer::class));

        $this->mapper->map([['product_id' => '1', 'product_name' => '  Phone  ']], TrimmedProductDto::class, 'product_id');
    }

    public function testNullNestedColumnsGiveNullObjectAndNullEagerCollection(): void
    {
        $rows = [
            self::productRow([
                'category_id' => null,
                'category_name' => null,
                'review_id' => null,
                'review_text' => null,
            ]),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertNull($result[0]->category);
        $this->assertNull($result[0]->reviews);
    }

    public function testNullNestedColumnsGiveEmptyLazyCollection(): void
    {
        $rows = [
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => null, 'review_text' => null, 'tag_id' => null, 'tag_name' => null],
        ];

        $result = $this->mapper->map($rows, ProductLazyDto::class, 'product_id');

        $this->assertInstanceOf(LazyCollection::class, $result[0]->reviews);
        $this->assertCount(0, $result[0]->reviews);
        $this->assertInstanceOf(LazyCollection::class, $result[0]->tags);
        $this->assertCount(0, $result[0]->tags);
    }

    public function testNestedObjectWithNullIdentifierIsSkippedEvenWhenOtherColumnsHaveValues(): void
    {
        $rows = [
            self::productRow(['category_id' => null, 'category_name' => 'Orphan']),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertNull($result[0]->category, 'A null identifier means "no entity", not an entity with id "".');
    }

    public function testCollectionItemWithNullIdentifierIsSkippedEvenWhenOtherColumnsHaveValues(): void
    {
        $rows = [
            self::productRow(['review_id' => null, 'review_text' => 'Orphan']),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertNull($result[0]->reviews);
    }

    public function testLazyCollectionItemWithNullIdentifierIsSkippedEvenWhenOtherColumnsHaveValues(): void
    {
        $rows = [
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => null, 'review_text' => 'Orphan', 'tag_id' => null, 'tag_name' => null],
        ];

        $result = $this->mapper->map($rows, ProductLazyDto::class, 'product_id');

        $this->assertInstanceOf(LazyCollection::class, $result[0]->reviews);
        $this->assertCount(0, $result[0]->reviews);
    }

    public function testNullObjectInLaterRowDoesNotOverwriteMappedObject(): void
    {
        $rows = [
            self::productRow(),
            self::productRow(['category_id' => null, 'category_name' => null, 'review_id' => '101']),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertNotNull($result[0]->category);
        $this->assertSame('10', $result[0]->category->id);
        $this->assertSame(['100', '101'], $this->ids($result[0]->reviews));
    }

    public function testDuplicateRowsAreMergedIntoSingleCollectionItem(): void
    {
        $rows = [self::productRow(), self::productRow(), self::productRow()];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertCount(1, $result);
        $this->assertSame(['100'], $this->ids($result[0]->reviews));
    }

    public function testSameNestedEntityIsSharedBetweenParents(): void
    {
        $rows = [
            self::productRow(['product_id' => '1']),
            self::productRow(['product_id' => '2']),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertCount(2, $result);
        $this->assertNotNull($result[0]->category);
        $this->assertSame($result[0]->category, $result[1]->category, 'Identity map returns one instance per (class, id).');
    }

    public function testRowsAreGroupedByRootIdKeepingFirstSeenOrder(): void
    {
        $rows = [
            self::productRow(['product_id' => '1', 'review_id' => '100']),
            self::productRow(['product_id' => '2', 'review_id' => '200']),
            self::productRow(['product_id' => '1', 'review_id' => '101']),
        ];

        $result = $this->mapper->map($rows, ProductDto::class, 'product_id');

        $this->assertCount(2, $result);
        $this->assertSame('1', $result[0]->id);
        $this->assertSame(['100', '101'], $this->ids($result[0]->reviews));
        $this->assertSame('2', $result[1]->id);
        $this->assertSame(['200'], $this->ids($result[1]->reviews));
    }

    public function testMapperCanBeReusedAndDoesNotShareIdentityMapBetweenCalls(): void
    {
        $first = $this->mapper->map([self::productRow()], ProductDto::class, 'product_id');
        $second = $this->mapper->map([self::productRow()], ProductDto::class, 'product_id');

        $this->assertNotSame($first[0], $second[0]);
        $this->assertNotSame($first[0]->category, $second[0]->category);
    }

    public function testLazyCollectionsAreInitializedIndependentlyAndOnlyOnAccess(): void
    {
        $rows = [
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => '100', 'review_text' => 'a', 'tag_id' => 't1', 'tag_name' => 'new'],
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => '100', 'review_text' => 'a', 'tag_id' => 't2', 'tag_name' => 'sale'],
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => '101', 'review_text' => 'b', 'tag_id' => 't1', 'tag_name' => 'new'],
            ['product_id' => '1', 'product_name' => 'Phone', 'review_id' => '101', 'review_text' => 'b', 'tag_id' => 't2', 'tag_name' => 'sale'],
        ];

        $result = $this->mapper->map($rows, ProductLazyDto::class, 'product_id');
        $reviews = $result[0]->reviews;
        $tags = $result[0]->tags;

        $this->assertInstanceOf(LazyCollection::class, $reviews);
        $this->assertInstanceOf(LazyCollection::class, $tags);
        $initialized = [];
        $initialized['before'] = [$reviews->isInitialized(), $tags->isInitialized()];

        $reviewIds = $this->ids($reviews);
        $initialized['after reviews'] = [$reviews->isInitialized(), $tags->isInitialized()];

        $tagIds = $this->ids($tags);
        $initialized['after tags'] = [$reviews->isInitialized(), $tags->isInitialized()];

        $this->assertSame(['100', '101'], $reviewIds);
        $this->assertSame(['t1', 't2'], $tagIds);
        $this->assertSame([
            'before' => [false, false],
            'after reviews' => [true, false], // touching one lazy collection must not initialize the other
            'after tags' => [true, true],
        ], $initialized);
    }

    public function testDtoWithoutIdentifierCannotBeMapped(): void
    {
        $this->expectException(InvalidArrayKayException::class);
        $this->expectExceptionMessage(\sprintf('there is no key name defined for scalar value in DTO class "%s".', NoIdentifierDto::class));

        $this->mapper->map([['item_id' => '1', 'item_name' => 'x']], NoIdentifierDto::class, 'item_id');
    }

    public function testScalarPropertyDeclaredAfterAdapterPropertyIsNotHydrated(): void
    {
        $result = $this->mapper->map([self::productRow()], ScalarAfterAdapterDto::class, 'product_id');

        $this->assertNotNull($result[0]->category, 'Nested object is still mapped.');
        $this->assertNull($result[0]->name, 'Scalar hydration stops at the first #[ObjectDto]/#[Collection] property.');
    }

    public function testExtractIdentifierThrowsWhenDtoHasNoIdentifierAttribute(): void
    {
        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionMessage('Attribute Identifier not found in Dto class stdClass');

        $this->mapper->extractIdentifier(new \stdClass());
    }

    /**
     * @param array<string, scalar|null> $overrides
     *
     * @return array<string, scalar|null>
     */
    private static function productRow(array $overrides = []): array
    {
        return \array_merge([
            'product_id' => '1',
            'product_name' => 'Phone',
            'product_full_title' => 'Phone Pro',
            'product_created_at' => '2026-01-01',
            'product_secret' => 'leaked',
            'category_id' => '10',
            'category_name' => 'Electronics',
            'review_id' => '100',
            'review_text' => 'Great',
        ], $overrides);
    }

    /**
     * @param iterable<array-key, mixed>|null $items
     *
     * @return list<string|null>
     */
    private function ids(iterable|null $items): array
    {
        $ids = [];

        foreach ($items ?? [] as $item) {
            \assert($item instanceof ReviewDto || $item instanceof TagDto);
            $ids[] = $item->id;
        }

        return $ids;
    }
}
