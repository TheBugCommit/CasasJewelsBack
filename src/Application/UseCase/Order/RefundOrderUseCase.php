<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\Service\PaymentServiceInterface;
use App\Domain\ValueObject\OrderStatus;
use Sce\CasasJewelsVendor\DTO\Requests\RefundOrder\RefundOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\RefundOrder\RefundOrderResponseDTO;

final class RefundOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentServiceInterface  $paymentService,
        private readonly OrderMapper              $orderMapper,
        private readonly UnitOfWorkInterface      $uow,
    ) {}

    public function execute(RefundOrderRequestDTO $request): RefundOrderResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $order->transitionTo(OrderStatus::RefundPending);

        if ($order->getTransactionId()) {
            $this->paymentService->refund($order->getTransactionId());
        }

        $this->orderRepository->save($order);
        $this->uow->commit();

        return $this->orderMapper->toRefundOrderResponseDTO($order);
    }
}
