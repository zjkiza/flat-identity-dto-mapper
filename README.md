# Flat Identity DTO Mapper

Build rich DTO graphs from flat SQL result sets — without an ORM. 
A high-performance mapper that converts flat database/array rows into structured DTO graphs using identity mapping and attribute adapters.

## ✨ Features

✅ Maps flat SQL JOIN results into deep DTO object graphs

✅ Identity Map (no duplicated objects, no data loss)

✅ Attribute-driven mapping

✅ Nested objects & collections

✅ Pluggable value transformers

✅ Circular reference safe

✅ High performance (reflection cached)

## Installation

```bach
composer require zjkiza/flat-identity-dto-mapper
```

## Core Concept

The Flat Identity DTO Mapper takes flat SQL result sets (e.g. from JOIN queries) and maps them into rich, nested DTO
object graphs. It uses an Identity Map to ensure that each unique entity is represented by a single object instance,
preventing duplicates and data loss.

SQL queries with JOINs return denormalized rows:

```
media_id | media_title | author_id | author_name | author_image_id
```

This mapper converts them into:

```php
MediaDto
 ├── ImageDto
 └── AuthorDto[]
      └── ImageDto
```

## DTO Definition Example

```php
#[ColumnPrefix(name: 'media_', naming: 'snakeToCamel')]
final class ArrayMediaDto implements JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    #[Transformer(UpperTransformer::class)]
    public ?string $title = null;

    #[ObjectDto(className: ImageMediaDto::class, columnPrefix: 'media_image_')]
    public ?ImageMediaDto $image = null;

    #[Collection(className: ArrayAuthorDto::class, columnPrefix: 'media_author_')]
    public ?array $author = null;

    public function jsonSerialize(): array
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'image'  => $this->image,
            'author' => array_values($this->author),
        ];
    }
}

```

## Ignore

If a scalar property should be ignored and not mapped to the DTO, insert the Ignore attribute. 
For properties of the Object/iterable type, you can only insert the necessary annotation that is provided for them.

Example:
```php
    #[Ignore()]
    public ?string $title = null;
```


## Transformers

Transformers allow value normalization during mapping.

```php
interface DataTransformerInterface
{
    public function transform(mixed $value): mixed;
}
```

Example

```php
final class UpperTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        return is_string($value) ? strtoupper($value) : null;
    }
}
```

Usage in DTO

```php
#[Transformer(UpperTransformer::class)]
public ?string $title = null;
```

## Mapping Process

```php
$mapper = new UniversalDtoMapper();
$result = $mapper->map($rows, ArrayMediaDto::class, 'media_id');
```

✔ Automatically groups rows

✔ Preserves identity

✔ Merges nested data

## 🔐 Identity Map

Each DTO instance exists only once per ID.

This guarantees:
- no overwritten nested data
- no missing images
- correct multi-row aggregation

## When to use this mapper?

✔ Complex SQL

✔ Heavy JOINs

✔ DTO-based API

✔ No ORM hydration overhead

❌ Not intended as a full ORM

## Testing

The mapper is deterministic and test-friendly.
Each SQL row always produces the same DTO graph.

## Performance Notes

- Reflection cached
- Identity Map prevents object churn
- Single-pass row processing

## Summary

This mapper is ideal when:
- You want ORM-level graph hydration
- without ORM-level complexity.

# Flow Chart (SQL → DTO Graph)

```sql
    +--------------------+
    |   SQL Result Set   |
    | (flat JOIN rows)   |
    +----------+---------+
             |
             v
    +--------------------+
    | Group by root ID   |
    | (exs. media_id)    |
    +----------+---------+
             |
             v
 +--------------------------+
 | UniversalDtoMapper       |
 |                          |
 |  - Identity Map          |
 |  - Attribute Adapters    |
 |  - Transformers          |
 +------------+-------------+
              |
    -------------------------
    |                       |
    v                       v
 Scalar Mapping       Nested Mapping
 (id, title)          (image, author[])
                            |
                            v
                     +-------------+
                     | IdentityMap |
                     | (Author)    |
                     +------+------+ 
                            |
                            v
                     +-------------+
                     | Image DTO   |
                     +-------------+

Final Output:
DTO Graph (no duplicates, fully hydrated)

```