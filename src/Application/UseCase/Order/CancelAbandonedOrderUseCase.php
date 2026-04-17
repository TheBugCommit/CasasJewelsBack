<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\OrderStatus;

final class CancelAbandonedOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UnitOfWorkInterface      $uow,
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order || $order->getStatus() !== OrderStatus::PendingPayment) {
            return;
        }

        $this->orderRepository->delete($order);
        $this->uow->commit();
    }
}
