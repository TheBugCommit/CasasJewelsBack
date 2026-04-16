<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Service\PaymentServiceInterface;
use App\Domain\ValueObject\OrderStatus;
use App\Infrastructure\Api\Mapper\OrderMapper;
use App\Infrastructure\Persistence\DoctrineUnitOfWork;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusResponseDTO;

final class RefundOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentServiceInterface  $paymentService,
        private readonly OrderMapper              $orderMapper, private readonly DoctrineUnitOfWork $doctrineUnitOfWork,
    ) {}

    public function execute(string $orderId): UpdateOrderStatusResponseDTO
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($orderId);
        }

        $order->transitionTo(OrderStatus::RefundPending);

        if ($order->getTransactionId()) {
            $this->paymentService->refund($order->getTransactionId());
        }

        $this->orderRepository->save($order);
        $this->doctrineUnitOfWork->commit();

        return $this->orderMapper->toUpdateOrderStatusResponseDTO($order);
    }
}
