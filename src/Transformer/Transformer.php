<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Transformer;

use ZJKiza\FlatMapper\Contract\DataTransformerInterface;
use ZJKiza\FlatMapper\Contract\TransformerInterface;
use ZJKiza\FlatMapper\Exception\InvalidObjectInstanceException;

final class Transformer implements TransformerInterface
{
    /**
     * @var array<DataTransformerInterface::class, DataTransformerInterface>
     */
    private array $transformers = [];

    public function __construct()
    {
        $this->addTransformer(new UpperTransformer());
    }

    public function transform(mixed $value, string $transformerClass): mixed
    {
        return $this
            ->getTransformer($transformerClass)
            ->transform($value);
    }

    public function addTransformer(DataTransformerInterface $transformer): void
    {
        $this->transformers[$transformer::class] = $transformer;
    }

    private function getTransformer(string $transformerClass): DataTransformerInterface
    {
        if (!isset($this->transformers[$transformerClass])) {
            throw new InvalidObjectInstanceException(\sprintf('Object with instance "%s" does not exist! Check that you have imported that instance into a Mapper class constructor.', $transformerClass));
        }

        return $this->transformers[$transformerClass];
    }
}
