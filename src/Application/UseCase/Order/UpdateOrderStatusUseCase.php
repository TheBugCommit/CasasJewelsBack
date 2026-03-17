<?php

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\ValueObject\OrderStatus;
use App\Infrastructure\Api\Mapper\OrderMapper;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusResponseDTO;

final class UpdateOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderMapper              $orderMapper,
    )
    {
    }

    public function execute(UpdateOrderStatusRequestDTO $request): UpdateOrderStatusResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if(!$order){
            throw OrderNotFoundException::withId($request->orderId);
        }

        $newStatus = OrderStatus::from($request->status);

        $order->transitionTo($newStatus);

        $this->orderRepository->save($order);

        return $this->orderMapper->toUpdateOrderStatusResponseDTO($order);
    }
}

