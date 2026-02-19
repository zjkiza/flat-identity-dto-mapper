<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Attribute\Transformer;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Transformer\UpperTransformer;

#[ColumnPrefix(name: 'media_', naming: Naming::CamelToSnake)]
final class MediaLazyDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    #[Transformer(UpperTransformer::class)]
    public ?string $title = null;

    #[ObjectDto(className: MediaImageLazyDto::class, columnPrefix: 'media_image_', naming: Naming::CamelToSnake)]
    public ?MediaImageLazyDto $image = null;

    #[Collection(className: AuthorLazyDto::class, columnPrefix: 'media_author_', naming: Naming::CamelToSnake, lazy: true)]
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
