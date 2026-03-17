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

    public function getMultiple(array $keys): array
    {
        if (empty($keys)) return [];

        $items = $this->adapter->getItems($keys);
        $found = [];

        foreach ($items as $key => $item) {
            if ($item->isHit()) {
                $found[$key] = $item->get();
            }
        }

        return $found;
    }

    public function setMultiple(array $items, int $ttl): void
    {
        if (empty($items)) return;

        foreach ($items as $key => $value) {
            $item = $this->adapter->getItem($key);
            $item->set($value);
            $item->expiresAfter($ttl);
            $this->adapter->saveDeferred($item);
        }

        $this->adapter->commit();
    }
}
