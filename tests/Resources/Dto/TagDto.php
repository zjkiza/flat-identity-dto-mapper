<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Dto;

use ZJKiza\FlatMapper\Attribute\Identifier;

final class TagDto
{
    #[Identifier]
    public ?string $id = null;

    public ?string $name = null;
}
