<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Application\Service\EmailQueueService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\OrderStatus;
use Sce\CasasJewelsVendor\DTO\Requests\ConfirmOrderRefunded\ConfirmOrderRefundedRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\ConfirmOrderRefunded\ConfirmOrderRefundedResponseDTO;

final class ConfirmOrderRefundedUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderMapper              $orderMapper,
        private readonly EmailQueueService        $emailQueueService,
        private readonly UnitOfWorkInterface      $uow,
    ) {}

    public function execute(ConfirmOrderRefundedRequestDTO $request): ConfirmOrderRefundedResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $order->transitionTo(OrderStatus::Cancelled);
        $this->orderRepository->save($order);
        $this->uow->commit();

        $this->emailQueueService->queueOrderCancelled($order->getId());

        return $this->orderMapper->toConfirmOrderRefundedResponseDTO($order);
    }
}
