<?php

namespace App\Infrastructure\Cache;

use Predis\Client;
final class RedisCache implements CacheInterface
{
    public function __construct(
        private readonly Client $redis,
    ) {}

    public function get(string $key): mixed
    {
        $raw = $this->redis->get($key);

        return is_string($raw) ? unserialize($raw) : null;
    }

    public function set(string $key, mixed $value, int $ttl): void
    {
        $this->redis->setex($key, $ttl, serialize($value));
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function getMultiple(array $keys): array
    {
        if (empty($keys)) return [];

        $rawValues = $this->redis->mget($keys);
        $found = [];

        foreach ($keys as $index => $key) {
            $raw = $rawValues[$index];
            if (is_string($raw)) {
                $found[$key] = unserialize($raw);
            }
        }

        return $found;
    }

    public function setMultiple(array $items, int $ttl): void
    {
        if (empty($items)) return;

        $this->redis->pipeline(function ($pipe) use ($items, $ttl) {
            foreach ($items as $key => $value) {
                $pipe->setex($key, $ttl, serialize($value));
            }
        });
    }
}
