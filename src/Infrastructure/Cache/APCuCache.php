<?php

namespace App\Infrastructure\Cache;

use Symfony\Component\Cache\Adapter\ApcuAdapter;

final class APCuCache implements CacheInterface
{
    public function __construct(
        private readonly ApcuAdapter $adapter
    ) {}

    public function get(string $key): mixed
    {
        $item = $this->adapter->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    public function set(string $key, mixed $value, int $ttl): void
    {
        $item = $this->adapter->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->adapter->save($item);
    }

    public function delete(string $key): void
    {
        $this->adapter->deleteItem($key);
    }
}
