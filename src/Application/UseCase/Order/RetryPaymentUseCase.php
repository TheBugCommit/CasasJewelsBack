<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Application\Service\OrderPaymentService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;

final class RetryPaymentUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderMapper              $orderMapper,
        private readonly UnitOfWorkInterface      $uow,
        private readonly OrderPaymentService      $orderPaymentService,
    ) {}

    public function execute(string $orderId): CreateOrderResponseDTO
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($orderId);
        }

        $this->orderPaymentService->generatePaymentForOrder($order);

        $this->orderRepository->save($order);
        $this->uow->commit();

        return $this->orderMapper->toCreateOrderResponseDTO($order);
    }
}
