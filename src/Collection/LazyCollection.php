<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Collection;

use ZJKiza\FlatMapper\Contract\LazyCollectionInterface;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements LazyCollectionInterface<TKey, TValue>
 */
final class LazyCollection implements LazyCollectionInterface
{
    private bool $initialized = false;

    /**
     * @var array<TKey, TValue>
     */
    private array $items = [];

    /**
     * @param \Closure(): array<TKey, TValue> $initializer
     */
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

    /**
     * @return \Traversable<TKey, TValue>
     */
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

    /**
     * @return array<TKey, TValue>
     */
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
