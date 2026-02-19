<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Metadata;

final readonly class DtoMetadata
{
    public function __construct(
        public \ReflectionClass $reflectionClass,
        /** @var \ReflectionProperty[] */
        public array            $properties
    ) {
    }
}
