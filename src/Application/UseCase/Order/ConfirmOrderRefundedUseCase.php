<?php
declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Service\EmailQueueService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\ValueObject\OrderStatus;
use App\Infrastructure\Persistence\DoctrineUnitOfWork;

final class ConfirmOrderRefundedUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EmailQueueService        $emailQueueService, private readonly DoctrineUnitOfWork $doctrineUnitOfWork,
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($orderId);
        }

        $order->transitionTo(OrderStatus::Cancelled);
        $this->orderRepository->save($order);
        $this->emailQueueService->queueOrderCancelled($order->getId());
        $this->doctrineUnitOfWork->commit();
    }
}
