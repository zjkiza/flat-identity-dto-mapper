# Flat Identity DTO Mapper

Build rich DTO graphs from flat SQL result sets — without an ORM.
A high-performance mapper that converts flat database/array rows into structured DTO graphs using identity mapping and attribute adapters.


## Key features

- Maps flat SQL JOIN results into deep DTO object graphs
- Identity Map (no duplicated objects, consistent merging)
- Attribute-driven mapping (PHP 8 Attributes)
- Nested objects & collections (including lazy collections)
- Pluggable value transformers
- Circular reference safe
- High performance (reflection cached)
- Low memory footprint when mapping large result sets (single-pass merging, identity map, lazy collections)


## Installation

Install via Composer:

```bash
composer require zjkiza/flat-identity-dto-mapper
```

Run tests locally (if you want to run the project's tests):

```bash
composer install
./vendor/bin/phpunit
```


## Quickstart

This mapper expects a flat array of associative rows (for example the result of a SQL JOIN) and a DTO class annotated with attributes which describe how columns map to DTO properties.

Important: the root DTO MUST define a `ColumnPrefix` attribute — this tells the mapper which column prefixes to use when extracting scalar values for that DTO and its nested children.

You may pass any flat associative array (for example rows returned by `PDO::fetchAll(PDO::FETCH_ASSOC)` or any other source that produces a list of maps). The mapper's only requirement is that column names follow the prefix conventions used in your DTO attributes (see `ColumnPrefix`, `ObjectDto`, `Collection`).

Example: rows (excerpt from `tests/Resources/Data/data.json`):

```php
$rows = json_decode(file_get_contents(__DIR__ . '/tests/Resources/Data/data.json'), true);
```

Database example (SQL)

Below is a real-world example of a JOIN query that returns a flat result set suitable for mapping with this library (column aliases use the `media_`, `media_image_`, `media_author_` prefixes used in the test DTOs):

```sql
SELECT
  media.id AS media_id,
  media.title AS media_title,
  media_image.id AS media_image_id,
  media_image.name AS media_image_name,
  media_image_tag.id AS media_image_tag_id,
  media_image_tag.name AS media_image_tag_name,
  author.id AS media_author_id,
  author.name AS media_author_name,
  author_image.id AS author_image_id,
  author_image.name AS author_image_name,
  author_image_tag.id AS author_image_tag_id,
  author_image_tag.name AS author_image_tag_name
FROM media AS media
  LEFT JOIN media_author AS mediaAuthor ON mediaAuthor.abstract_media_id = media.id
  LEFT JOIN expert AS author ON author.id = mediaAuthor.expert_id
  LEFT JOIN image AS media_image ON media_image.id = media.image_id
  LEFT JOIN image_tag ON image_tag.image_id = media_image.id
  LEFT JOIN tag AS media_image_tag ON media_image_tag.id = image_tag.tag_id
  LEFT JOIN image AS author_image ON author_image.id = author.image_id
  LEFT JOIN image_tag AS image_tag_auth ON image_tag_auth.image_id = author_image.id
  LEFT JOIN tag AS author_image_tag ON author_image_tag.id = image_tag_auth.tag_id;
```

PDO fetch example (how to obtain a compatible PHP array of rows):

```php
$pdo = new \PDO($dsn, $user, $pass, $options);
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // flat associative arrays

$mapper = new ZJKiza\FlatMapper\UniversalDtoMapper();
$dto = $mapper->map($rows, \ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto::class, 'media_id');
```

Doctrine example:

```php

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractMedia::class);
    }
    
    public function getRows(): array
    {
        $sql = 'SELECT ...'; // same SQL as above
        $stmt = $this->entityManager->getConnection()->executeQuery($sql);
        
        return $stmt->fetchAllAssociative(); // flat associative arrays
    }
}

use ZJKiza\FlatMapper\UniversalDtoMapper;

class TestController
{
    public function index(MediaRepository $repository, UniversalDtoMapper $mapper)
    {
        $rows = $repository->getRows();
        $dto = $mapper->map($rows, MediaDto::class, 'media_id');
        return new JsonResponse($dto);
    }
}

```


Basic usage — map flat rows to DTO graph:

```php
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto;

$mapper = new UniversalDtoMapper();
$dto = $mapper->map($rows, MediaDto::class, 'media_id');

echo json_encode($dto, JSON_THROW_ON_ERROR);
```

This repository includes a functional test that maps the provided test rows into the expected JSON graph. The mapper will group rows by the "root id" (here `media_id`), create DTO instances, and merge nested/scalar values while preserving identity.


## DTO definition (attributes)

DTOs use PHP Attributes to describe mapping rules. Example DTO (from tests):

```php
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\Transformer;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

#[ColumnPrefix(name: 'media_', naming: Naming::CamelToSnake)]
final class MediaDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    #[Transformer(UpperTransformer::class)]
    public ?string $title = null;

    #[ObjectDto(className: MediaImageDto::class, columnPrefix: 'media_image_', naming: Naming::CamelToSnake)]
    public ?MediaImageDto $image = null;

    #[Collection(className: AuthorDto::class, columnPrefix: 'media_author_', naming: Naming::CamelToSnake)]
    public iterable|null $author = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'author' => \array_values(\iterator_to_array($this->author)),
        ];
    }
}
```

Attribute reference:

- ColumnPrefix(name: string, naming: Naming) — required on the root DTO. Defines the column prefix for root scalar properties and default naming strategy.
- Identifier — mark the property that holds the unique identifier for the entity (used by IdentityMap).
- Column(name: string) — map a property to a different column name under the active column prefix.
- ObjectDto(className: string, columnPrefix: string, naming: Naming) — map a nested object; a new sub-prefix can be used.
- Collection(className: string, columnPrefix: string, naming: Naming, lazy: bool = false) — map a collection of nested objects (supports lazy collections).
- Transformer(className: string) — apply a value transformer when setting the property.
- Ignore — skip a scalar property from mapping.

Notes:
- Naming strategies (CamelToSnake / SnakeToCamel) help convert column names to property names when using default names.
- The root DTO MUST define a ColumnPrefix attribute.


## Built-in transformers

Transformers implement `ZJKiza\FlatMapper\Contract\DataTransformerInterface` (single method: `transform(mixed $value): mixed`).

Example built-in transformer (UpperTransformer):

```php
final class UpperTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\\is_string($value)) {
            return \\strtoupper($value);
        }

        throw TransformationFailedException::create($value, $this);
    }
}
```

Apply transformer on a DTO property:

```php
#[Transformer(UpperTransformer::class)]
public ?string $title = null;
```


## Extending with custom transformers

To create a custom transformer, implement `DataTransformerInterface`:

```php
use ZJKiza\FlatMapper\Contract\DataTransformerInterface;

final class TrimAndUpperTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return strtoupper(trim($value));
        }

        return $value;
    }
}
```

Registering your transformer depends on your framework (see below). The mapper's transformer resolver will instantiate the class when needed if it can be autoloaded/in the container.


## Collections & Lazy Collections

Collections are defined using `Collection` attribute on an iterable property. By default the mapper will populate a PHP array. If `lazy: true` is specified, the mapper returns a `LazyCollection` that defers iteration until needed.

Example (lazy collection):

```php
#[Collection(className: AuthorLazyDto::class, columnPrefix: 'media_author_', naming: Naming::CamelToSnake, lazy: true)]
public iterable|null $author = null;
```

In tests, both eager and lazy collections are exercised and produce the same final JSON output.


## Identity Map

The mapper uses an Identity Map to guarantee that entities with the same identifier produce a single object instance across the whole mapped graph. This prevents duplicated objects when multiple rows refer to the same nested entity.

API is internal to the mapper; however the identity behavior can be observed in test fixtures where nested images and authors are shared across rows.


## Errors and exceptions

The mapper throws specific exceptions for common errors:

- InvalidArrayKayException — when the provided root id is missing from rows or a required column is not present.
- InvalidAttributeException — when required attributes (like ColumnPrefix) are missing from DTO classes.
- InvalidObjectInstanceException — when a nested object cannot be instantiated with expected type.
- TransformationFailedException — thrown by transformers when an unexpected value is provided.


## Symfony integration (example)

Below is a minimal example for registering the mapper and a custom transformer as services in `services.yaml`.

```yaml
services:

  App\Transformer\TrimAndUpperTransformer:
    tags: ['app.dto_transformer']

  # Transformer servis
  ZJKiza\FlatMapper\Transformer:
    arguments:
        $transformers: !tagged_iterator app.dto_transformer

  # Mapper
  ZJKiza\FlatMapper\UniversalDtoMapper:
    arguments:
        $transformer: '@App\Transformer\Transformer'

  # Alias the interface to the implementation for easier injection
  ZJKiza\FlatMapper\Contract\UniversalDtoMapperInterface: '@ZJKiza\FlatMapper\UniversalDtoMapper'

```

Example controller usage:

```php
public function index(UniversalDtoMapper $mapper)
{
    $rows = /* fetch rows from DB */;
    $dto = $mapper->map($rows, MediaDto::class, 'media_id');
    return new JsonResponse($dto);
}
```


## Laravel integration (example)

You can register the mapper and custom transformers in a ServiceProvider (example for Laravel 8+):

```php
use Illuminate\Support\ServiceProvider;
use App\Transformer\TrimAndUpperTransformer;
use ZJKiza\FlatMapper\Transformer;
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Contract\UniversalDtoMapperInterface;

class MapperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->tag(TrimAndUpperTransformer::class, 'flat_dto_transformer');
        
        $this->app->singleton(Transformer::class, function ($app) {
            $transformers = $app->tagged('flat_dto_transformer');
            return new Transformer($transformers);
        });
        
        $this->app->singleton(UniversalDtoMapper::class, function ($app) {
            return new UniversalDtoMapper($app->make(Transformer::class));
        });
        
        $this->app->alias(UniversalDtoMapper::class, UniversalDtoMapperInterface::class);
    }
}
```

Usage in a controller:

```php

use ZJKiza\FlatMapper\Contract\UniversalDtoMapperInterface;

public function index(UniversalDtoMapperInterface $mapper)
{
    $rows = /* fetch rows */;
    $dto = $mapper->map($rows, MediaDto::class, 'media_id');
    return response()->json($dto);
}
```


## Practical example (full mapping snippet from tests)

This is a compact example using the DTOs and data bundled with the tests.

```php
use ZJKiza\FlatMapper\UniversalDtoMapper;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaDto;

$rows = json_decode(file_get_contents(__DIR__ . '/tests/Resources/Data/data.json'), true);
$mapper = new UniversalDtoMapper();
$dto = $mapper->map($rows, MediaDto::class, 'media_id');
echo json_encode($dto, JSON_THROW_ON_ERROR);
// Output equals tests/Resources/Data/expected.json
```


## Troubleshooting & tips

- Ensure your root DTO defines `ColumnPrefix` attribute.
- If you see duplicates in nested arrays, confirm the `Identifier` attribute is present on nested DTOs.
- Use `Column(name: '...')` when a column name does not match the property name after the naming strategy.
- For complex DI needs (transformer factories, custom adapter wiring), register your own `Transformer` service and inject it into a small factory that returns a configured `UniversalDtoMapper`.


## Contribution

Contributions, bug reports and PRs are welcome. Please run the project's test suite and ensure new changes have unit tests.
