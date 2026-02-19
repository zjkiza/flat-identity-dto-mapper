<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Collection;

use ZJKiza\FlatMapper\Contract\LazyCollectionInterface;

final class LazyCollection implements LazyCollectionInterface
{
    private bool $initialized = false;
    private array $items = [];

    public function __construct(
        private readonly \Closure $initializer
    ) {
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->items = ($this->initializer)();
        $this->initialized = true;
    }

    public function getIterator(): \Traversable
    {
        $this->initialize();

        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        $this->initialize();

        return \count($this->items);
    }

    public function toArray(): array
    {
        $this->initialize();

        return $this->items;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}
