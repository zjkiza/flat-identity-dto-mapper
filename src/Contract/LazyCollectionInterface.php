<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Contract;

interface LazyCollectionInterface extends \IteratorAggregate, \Countable
{
    public function isInitialized(): bool;
}
