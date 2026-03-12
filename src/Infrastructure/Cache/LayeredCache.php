<?php

namespace App\Infrastructure\Cache;

final class LayeredCache
{
    public function __construct(
        private readonly APCuCache  $apcu,
        private readonly RedisCache $redis,
    ) {}

    /**
     * Patrón APCu → Redis → fallback, reutilizable en cualquier caso de uso.
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
        // Nivel 1: APCu
        $value = $this->apcu->get($key);
        if ($value !== null) {

            return $value;
        }

        // Nivel 2: Redis → warm-up APCu
        $value = $this->redis->get($key);
        if ($value !== null) {
            $this->apcu->set($key, $value, $apCuTtl);

            return $value;
        }

        // Nivel 3: fallback (BD) → guardar en ambas capas
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
