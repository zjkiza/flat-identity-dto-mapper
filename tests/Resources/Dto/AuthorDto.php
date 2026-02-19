<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;

final class AuthorDto
{
    #[Identifier]
    public ?string $id;
    public ?string $name;

    #[ObjectDto(className: AuthorImageDto::class, columnPrefix: 'author_image_', naming: 'snakeToCamel')]
    public ?AuthorImageDto $image = null;
}
