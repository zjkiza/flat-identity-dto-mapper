<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface LazyCollectionInterface extends \IteratorAggregate, \Countable
{
    public function isInitialized(): bool;
}
