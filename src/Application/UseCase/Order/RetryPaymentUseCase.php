<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\Service\PaymentServiceInterface;
use App\Domain\ValueObject\OrderStatus;
use App\Infrastructure\Api\Mapper\OrderMapper;
use App\Infrastructure\Persistence\DoctrineUnitOfWork;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;

final class RetryPaymentUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentServiceInterface  $paymentService,
        private readonly OrderMapper              $orderMapper,
        private readonly UnitOfWorkInterface      $uow,
    ) {}

    public function execute(string $orderId): CreateOrderResponseDTO
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($orderId);
        }

        $paymentData = $this->paymentService->initiate(
            $order->getId(),
            $order->getClientEmail(),
            $order->getTotalAmount()
        );

        $order->assignPaymentDetails($paymentData['transactionId'], $paymentData['paymentUrl']);
        $this->orderRepository->save($order);
        $this->uow->commit();

        return $this->orderMapper->toCreateOrderResponseDTO($order);
    }
}
