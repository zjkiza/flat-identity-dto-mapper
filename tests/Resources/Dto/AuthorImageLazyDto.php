<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\Column;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Tests\Resources\Dto\TagDto;

final class AuthorImageLazyDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    #[Column('name')]
    public ?string $url = null;

    #[Collection(className: TagDto::class, columnPrefix: 'author_image_tag_', naming: Naming::CamelToSnake, lazy: true)]
    public iterable|null $tag = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'tag' => null === $this->tag ? [] : \array_values(\iterator_to_array($this->tag)),
        ];
    }
}
