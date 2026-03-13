<?php

namespace App\Infrastructure\Cache;

final class LayeredCache
{
    public function __construct(
        private readonly APCuCache  $apcu,
        private readonly RedisCache $redis,
    ) {}

    /**
     * APCu → Redis → fallback
     *
     * @template T
     * @param callable(): T $fallback
     * @return T
     */
    public function remember(
        string   $key,
        int      $apCuTtl,
        int      $redisTtl,
        callable $fallback,
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

        $value = $fallback();
        $this->store($key, $value, $apCuTtl, $redisTtl);

        return $value;
    }

    public function store(string $key, mixed $value, int $apCuTtl, int $redisTtl): void
    {
        $this->apcu->set($key, $value, $apCuTtl);
        $this->redis->set($key, $value, $redisTtl);
    }

    public function invalidate(string $key): void
    {
        $this->apcu->delete($key);
        $this->redis->delete($key);
    }
}
