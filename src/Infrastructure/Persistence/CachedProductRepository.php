<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Cache\LayeredCache;

final class CachedProductRepository implements ProductRepositoryInterface
{
    private const IDS_APCU  = 60;
    private const IDS_REDIS = 300;
    private const PRODUCT_APCU  = 300;
    private const PRODUCT_REDIS = 3600;
    private const COUNT_APCU  = 120;
    private const COUNT_REDIS = 600;

    public function __construct(
        private readonly ProductRepositoryInterface $inner,
        private readonly LayeredCache               $cache,
    ) {}

    public function findIdsPaginated(int $limit, int $offset): array
    {
        $key = "products:ids:{$offset}:{$limit}";
        $pageIds = $this->cache->get($key, self::IDS_APCU);
        if(!$pageIds) {
            $pageIds = $this->inner->findIdsPaginated($limit, $offset);
            $this->cache->set($key, $pageIds, self::IDS_APCU, self::IDS_REDIS);
        }
        return $pageIds;
    }

    public function findByIds (array $ids): array
    {
        if (empty($ids)) return [];

        $keysToIds = [];
        foreach ($ids as $id) {
            $keysToIds["product:stable:{$id}"] = $id;
        }

        $keysToSearch = array_keys($keysToIds);
        $cachedItems = $this->cache->getMultiple($keysToSearch, self::PRODUCT_APCU);

        $result = [];
        foreach ($cachedItems as $key => $product) {
            $id = $keysToIds[$key];
            $result[$id] = $product;
        }

        $missingIds = array_values(array_diff($ids, array_keys($result)));
        if (!empty($missingIds)) {
            $fromDb = $this->inner->findByIds($missingIds);
            $toCache = [];
            foreach ($fromDb as $product) {
                $id = $product->getId();
                $result[$id] = $product;
                $toCache["product:stable:{$id}"] = $product;
            }

            if (!empty($toCache)) {
                $this->cache->setMultiple($toCache, self::PRODUCT_APCU, self::PRODUCT_REDIS);
            }
        }

        return array_values(
            array_filter(array_map(fn(string $id) => $result[$id] ?? null, $ids))
        );
    }

    public function countAll(): int
    {
        $key = "products:count";
        $productCount = $this->cache->get($key, self::COUNT_APCU);
        if(!$productCount) {
            $productCount = $this->inner->countAll();
            $this->cache->set($key,$productCount, self::COUNT_APCU, self::COUNT_REDIS);
        }
        return $productCount;
    }

    public function findById(string $id): Product
    {
        $key = "product:stable:{$id}";
        $product = $this->cache->get($key, self::PRODUCT_APCU);
        if(!$product) {
            $product = $this->inner->findById($id);
            $this->cache->set($key,$product, self::PRODUCT_APCU, self::PRODUCT_REDIS);
        }
        return $product;
    }
}
