<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Cache\LayeredCache;

final class CachedProductRepository implements ProductRepositoryInterface
{
    private const IDS_APCU  = 60;
    private const int IDS_REDIS = 300;
    private const int PRODUCT_APCU  = 300;
    private const int PRODUCT_REDIS = 3600;
    private const int COUNT_APCU  = 120;
    private const int COUNT_REDIS = 600;
    private const int LIST_VERSION = 86400;

    public function __construct(
        private readonly ProductRepositoryInterface $inner,
        private readonly LayeredCache               $cache,
    ) {}

    public function findIdsPaginated(int $limit, int $offset): array
    {
        $v = $this->getListVersion();
        $key = "products_ids_v{$v}_{$offset}_{$limit}";
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
            $keysToIds["product_stable_{$id}"] = $id;
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
                $toCache["product_stable_{$id}"] = $product;
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
        $v = $this->getListVersion();
        $key = "products_count_v{$v}";
        $productCount = $this->cache->get($key, self::COUNT_APCU);
        if(!$productCount) {
            $productCount = $this->inner->countAll();
            $this->cache->set($key,$productCount, self::COUNT_APCU, self::COUNT_REDIS);
        }
        return $productCount;
    }

    public function findById(string $id): Product|null
    {
        $key = "product_stable_{$id}";
        $product = $this->cache->get($key, self::PRODUCT_APCU);
        if(!$product) {
            $product = $this->inner->findById($id);
            if($product) {
                $this->cache->set($key, $product,self::PRODUCT_APCU, self::PRODUCT_REDIS);
            }
        }

        return $product;
    }

    public function save(Product $product): void
    {
        $this->inner->save($product);
        $this->bumpListVersion();
    }

    public function delete(Product $product): void
    {
        $this->inner->delete($product);
        $this->cache->invalidate("product_stable_{$product->getId()}");
        $this->cache->invalidate('products_count');
        $this->bumpListVersion();
    }

    private function getListVersion(): int
    {
        $version = $this->cache->getGlobal('products_list_version');
        return (int) ($version ?: 0);
    }

    private function bumpListVersion(): void
    {
        $newVersion = $this->getListVersion() + 1;
        $this->cache->setGlobal('products_list_version', $newVersion, self::LIST_VERSION);
    }
}
