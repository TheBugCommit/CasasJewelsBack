<?php

namespace App\Infrastructure\Cache;

final class LayeredCache
{
    public function __construct(
        private readonly CacheInterface  $apcu,
        private readonly CacheInterface $redis,
    ) {}

    public function get(
        string   $key,
        int      $apCuTtl,
    ): mixed {
        $value = $this->apcu->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $this->redis->get($key);
        if ($value !== null) {
            $this->apcu->set($key, $value, $apCuTtl);
            return $value;
        }

        return null;
    }

    public function getGlobal(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function setGlobal(string $key, mixed $value, int $ttl): void
    {
        $this->redis->set($key, $value, $ttl);
        $this->apcu->delete($key);
    }

    public function set(string $key, mixed $value, int $apCuTtl, int $redisTtl): void
    {
        $this->apcu->set($key, $value, $apCuTtl);
        $this->redis->set($key, $value, $redisTtl);
    }

    public function getMultiple(array $keys, int $apCuTtl): array
    {
        if (empty($keys)) return [];

        $result = $this->apcu->getMultiple($keys);
        $missingKeys = array_values(array_diff($keys, array_keys($result)));
        if (empty($missingKeys)) {
            return $result;
        }

        $redisFound = $this->redis->getMultiple($missingKeys);
        if (!empty($redisFound)) {
            $this->apcu->setMultiple($redisFound, $apCuTtl);
            $result = array_merge($result, $redisFound);
        }

        return $result;
    }

    public function setMultiple(array $values, int $apCuTtl, int $redisTtl): void
    {
        if (empty($values)) return;

        $this->apcu->setMultiple($values, $apCuTtl);
        $this->redis->setMultiple($values, $redisTtl);
    }

    public function invalidate(string $key): void
    {
        $this->apcu->delete($key);
        $this->redis->delete($key);
    }
}
