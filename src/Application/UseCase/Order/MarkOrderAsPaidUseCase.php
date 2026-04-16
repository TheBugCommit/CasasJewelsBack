<?php

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Service\EmailQueueService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\OrderStatus;

final readonly class MarkOrderAsPaidUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private EmailQueueService        $emailQueueService,
        private UnitOfWorkInterface      $uow,
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orders->findById($orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($orderId);
        }

        $order->transitionTo(OrderStatus::Paid);
        $this->orders->save($order);
        $this->uow->commit();
        $this->emailQueueService->queueOrderPaid($orderId);
    }
}
