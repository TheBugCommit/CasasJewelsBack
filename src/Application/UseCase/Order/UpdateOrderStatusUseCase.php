<?php

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Service\EmailQueueService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\ValueObject\OrderStatus;
use App\Infrastructure\Api\Mapper\OrderMapper;
use App\Infrastructure\Persistence\DoctrineUnitOfWork;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusResponseDTO;

final class UpdateOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderMapper              $orderMapper,
        private readonly EmailQueueService        $emailQueueService, private readonly DoctrineUnitOfWork $doctrineUnitOfWork,
    )
    {
    }

    public function execute(UpdateOrderStatusRequestDTO $request): UpdateOrderStatusResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $newStatus = OrderStatus::from($request->status);
        $order->transitionTo($newStatus);
        $this->orderRepository->save($order);
        $this->doctrineUnitOfWork->commit();

        if ($newStatus === OrderStatus::Delivering) {
            $this->emailQueueService->queueOrderDelivering($order->getId());
        }
        if ($newStatus === OrderStatus::Delivered) {
            $this->emailQueueService->queueOrderDelivered($order->getId());
        }

        return $this->orderMapper->toUpdateOrderStatusResponseDTO($order);
    }
}

