<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Collection;
use ZJKiza\FlatMapper\Attribute\ColumnPrefix;
use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Enum\Naming;

#[ColumnPrefix(name: 'media_', naming: Naming::CamelToSnake)]
final class MediaTestScalarDto implements \JsonSerializable
{
    #[Identifier]
    public ?string $id = null;

    public ?string $title = null;

    public ?string $description = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
