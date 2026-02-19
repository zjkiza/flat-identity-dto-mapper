<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Identity;

final class IdentityMap
{
    /**
     * @var array<string, object>
     *
     * key = className:id
     */
    private array $storage = [];

    public function has(string $class, string $id): bool
    {
        return isset($this->storage[$this->key($class, $id)]);
    }

    public function get(string $class, string $id): object
    {
        return $this->storage[$this->key($class, $id)];
    }

    public function put(string $class, string $id, object $object): void
    {
        $this->storage[$this->key($class, $id)] = $object;
    }

    private function key(string $class, string $id): string
    {
        return \sprintf("%s:%s", $class, $id);
    }

    public function count(): int
    {
        return \count($this->storage);
    }
}
