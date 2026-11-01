<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Tests\Resources\Transformer;

use ZJKiza\FlatMapper\Contract\DataTransformerInterface;
use ZJKiza\FlatMapper\Exception\TransformationFailedException;

final class TrimTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            return \trim($value);
        }

        throw TransformationFailedException::create($value, $this);
    }
}
