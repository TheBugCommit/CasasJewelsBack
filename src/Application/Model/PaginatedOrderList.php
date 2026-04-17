<?php

namespace App\Application\Model;

use App\Domain\Entity\Order;

final class PaginatedOrderList
{
    /**
     * @param array<int, Order> $items
     * @param int $totalItems
     */
    public function __construct(
        public readonly array $items,
        public readonly int $totalItems,
    ) {}
}
