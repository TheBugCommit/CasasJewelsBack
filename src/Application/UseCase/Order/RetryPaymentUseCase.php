<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Application\Service\OrderPaymentService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use Sce\CasasJewelsVendor\DTO\Requests\RetryPayment\RetryPaymentRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\RetryPayment\RetryPaymentResponseDTO;

final class RetryPaymentUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderMapper              $orderMapper,
        private readonly UnitOfWorkInterface      $uow,
        private readonly OrderPaymentService      $orderPaymentService,
    ) {}

    public function execute(RetryPaymentRequestDTO $request): RetryPaymentResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $this->orderPaymentService->generatePaymentForOrder($order);

        $this->orderRepository->save($order);
        $this->uow->commit();

        return $this->orderMapper->toRetryPaymentResponseDTO($order);
    }
}
