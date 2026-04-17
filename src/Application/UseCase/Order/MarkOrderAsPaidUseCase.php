<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Application\Service\EmailQueueService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\OrderStatus;
use Sce\CasasJewelsVendor\DTO\Requests\MarkOrderAsPaid\MarkOrderAsPaidRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\MarkOrderAsPaid\MarkOrderAsPaidResponseDTO;

final class MarkOrderAsPaidUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderMapper              $orderMapper,
        private readonly EmailQueueService        $emailQueueService,
        private readonly UnitOfWorkInterface      $uow,
    ) {}

    public function execute(MarkOrderAsPaidRequestDTO $request): MarkOrderAsPaidResponseDTO
    {
        $order = $this->orders->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $order->transitionTo(OrderStatus::Paid);
        $this->orders->save($order);
        $this->uow->commit();
        $this->emailQueueService->queueOrderPaid($request->orderId);

        return $this->orderMapper->toMarkOrderAsPaidResponseDTO($order);
    }
}
