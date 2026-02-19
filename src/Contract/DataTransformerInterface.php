<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

/**
 * Transforms a value between different representations.
 */
interface DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     * Value -> Dto.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * throws TransformationFailedException when the transformation fails
     */
    public function transform(mixed $value): mixed;
}
