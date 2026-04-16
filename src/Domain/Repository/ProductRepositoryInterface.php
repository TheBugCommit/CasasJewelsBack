<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function findIdsPaginated(int $limit, int $offset): array;

    /** @return Product[] */
    public function findByIds(array $ids): array;

    public function findById(string $id): Product|null;

    public function countAll(): int;

    public function save(Product $product): void;

    public function delete(Product $product): void;
}
