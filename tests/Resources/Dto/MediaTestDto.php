<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Enum\Naming;

#[ColumnPrefix(name: 'media_', naming: Naming::CamelToSnake)]
final class MediaTestDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    public ?string $title = null;

    public ?string $description = null;

    #[Collection(className: AuthorTestDto::class, columnPrefix: 'media_author_', naming: Naming::CamelToSnake, lazy: false)]
    public iterable|null $author = null;

    #[Collection(className: TagTestDto::class, columnPrefix: 'media_tag_', naming: Naming::CamelToSnake, lazy: false)]
    public iterable|null $tag = null;

    #[ObjectDto(className: ImageTestDto::class, columnPrefix: 'media_image_', naming: Naming::CamelToSnake)]
    public ?ImageTestDto $image = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => \array_values(\iterator_to_array($this->author)),
            'tag' => \array_values(\iterator_to_array($this->tag)),
            'image' => $this->image,
        ];
    }
}
