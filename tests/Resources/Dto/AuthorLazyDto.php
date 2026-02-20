<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Identifier;
use ZJKiza\FlatMapper\Attribute\ObjectDto;
use ZJKiza\FlatMapper\Enum\Naming;
use ZJKiza\FlatMapper\Tests\Resources\Dto\AuthorImageDto;

final class AuthorLazyDto
{
    #[Identifier]
    public ?string $id = null;
    public ?string $name = null;

    #[ObjectDto(className: AuthorImageLazyDto::class, columnPrefix: 'author_image_', naming: Naming::CamelToSnake)]
    public ?AuthorImageLazyDto $image = null;
}
