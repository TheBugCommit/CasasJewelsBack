<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Order;
use App\Domain\Model\PaginatedOrderList;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(string $id): Order|null;

    public function findPaginated(int $limit, int $offset): PaginatedOrderList;
}
