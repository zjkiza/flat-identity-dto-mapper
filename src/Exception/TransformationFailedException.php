<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Exception;

use ZJKiza\FlatMapper\Contract\DataTransformerInterface;
use ZJKiza\FlatMapper\Contract\ExceptionInterface;

final class TransformationFailedException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function create(mixed $input, DataTransformerInterface $transformer, ?\Throwable $previous = null): self
    {
        $inputType = \get_debug_type($input);
        $message = \sprintf('Unable to transform value of type "%s" using "%s" transformer.', $inputType, $transformer::class);

        return new self($message, $previous);
    }
}
