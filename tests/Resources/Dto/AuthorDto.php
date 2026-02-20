<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Enum\Naming;

final class AuthorDto
{
    #[Identifier]
    public ?string $id = null;
    public ?string $name = null;

    #[ObjectDto(className: AuthorImageDto::class, columnPrefix: 'author_image_', naming: Naming::CamelToSnake)]
    public ?AuthorImageDto $image = null;
}
