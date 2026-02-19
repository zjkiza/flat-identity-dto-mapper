<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Attribute\Transformer;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

#[ColumnPrefix(name: 'media_', naming: 'snakeToCamel')]
final class MediaDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    #[Transformer(UpperTransformer::class)]
    public ?string $title = null;

    #[ObjectDto(className: MediaImageDto::class, columnPrefix: 'media_image_', naming: 'snakeToCamel')]
    public ?MediaImageDto $image = null;

    #[Collection(className: AuthorDto::class, columnPrefix: 'media_author_', naming: 'snakeToCamel', lazy: true)]
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
