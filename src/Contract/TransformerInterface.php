<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

interface TransformerInterface
{
    public function transform(mixed $value, string $transformerClass): mixed;

    public function addTransformer(DataTransformerInterface $transformer): void;
}
