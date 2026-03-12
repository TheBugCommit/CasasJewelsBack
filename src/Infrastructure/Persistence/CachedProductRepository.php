<?php

// src/Infrastructure/Repository/CachedProductRepository.php
namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Cache\LayeredCache;

/**
 * Decorator sobre ProductRepositoryInterface.
 * El UseCase solo ve la interfaz — no sabe que hay cache aquí.
 *
 * Estrategia de keys:
 *   products:ids:{page}:{perPage}  → lista de IDs de una página (TTL corto)
 *   product:stable:{id}            → datos del producto (TTL largo, compartido entre páginas)
 *   products:count                 → total de registros
 */
final class CachedProductRepository implements ProductRepositoryInterface
{
    // Lista de IDs por página: expira pronto por si hay altas/bajas
    private const IDS_APCU  = 60;
    private const IDS_REDIS = 300;

    // Datos del producto: estables, TTL largo
    private const PRODUCT_APCU  = 300;
    private const PRODUCT_REDIS = 3600;

    // Total de productos
    private const COUNT_APCU  = 120;
    private const COUNT_REDIS = 600;

    public function __construct(
        private readonly ProductRepositoryInterface $inner,
        private readonly LayeredCache               $cache,
    ) {}

    public function findIdsPaginated(int $limit, int $offset): array
    {
        return $this->cache->remember(
            key:      "products:ids:{$offset}:{$limit}",
            apCuTtl:  self::IDS_APCU,
            redisTtl: self::IDS_REDIS,
            fallback: fn() => $this->inner->findIdsPaginated($limit, $offset),
        );
    }

    public function findByIds(array $ids): array
    {
        $result  = [];
        $missing = [];

        foreach ($ids as $id) {
            $cached = $this->cache->remember(
                key:      "product:stable:{$id}",
                apCuTtl:  self::PRODUCT_APCU,
                redisTtl: self::PRODUCT_REDIS,
                fallback: fn() => null,
            );

            if ($cached instanceof Product) {
                $result[$id] = $cached;
            } else {
                $missing[] = $id;
            }
        }

        if (!empty($missing)) {
            $fromDb = $this->inner->findByIds($missing);
            foreach ($fromDb as $product) {
                $this->cache->remember(
                    key:      "product:stable:{$product->getId()}",
                    apCuTtl:  self::PRODUCT_APCU,
                    redisTtl: self::PRODUCT_REDIS,
                    fallback: fn() => $product,
                );
                $result[$product->getId()] = $product;
            }
        }


        return array_values(
            array_filter(array_map(fn(string $id) => $result[$id] ?? null, $ids))
        );
    }

    public function countAll(): int
    {
        return $this->cache->remember(
            key:      'products:count',
            apCuTtl:  self::COUNT_APCU,
            redisTtl: self::COUNT_REDIS,
            fallback: fn() => $this->inner->countAll(),
        );
    }
}
