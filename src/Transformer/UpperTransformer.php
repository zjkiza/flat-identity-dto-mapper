<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Transformer;

use ZJKiza\FlatMapper\Contract\DataTransformerInterface;
use ZJKiza\FlatMapper\Exception\TransformationFailedException;

final class UpperTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            return \strtoupper($value);
        }

        throw TransformationFailedException::create($value, $this);
    }
}
