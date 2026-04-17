<?php

namespace App\Domain\Repository;

use App\Application\Model\PaginatedOrderList;
use App\Domain\Entity\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(string $id): Order|null;
    public function findByIds(array $ids): array;

    public function findPaginated(int $limit, int $offset): PaginatedOrderList;
}
