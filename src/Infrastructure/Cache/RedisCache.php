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
}
