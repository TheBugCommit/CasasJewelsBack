<?php

namespace App\Application\UseCase\Order;

use App\Application\Mappers\OrderMapper;
use App\Application\Service\EmailQueueService;
use App\Domain\Factory\OrderFactory;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\Service\PaymentServiceInterface;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;

final class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderMapper                $orderMapper,
        private readonly OrderRepositoryInterface   $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly EmailQueueService          $emailQueueService,
        private readonly OrderFactory               $orderFactory,
        private readonly PaymentServiceInterface    $paymentService,
        private readonly UnitOfWorkInterface        $uow,
    ) {}

    public function execute(CreateOrderRequestDTO $request): CreateOrderResponseDTO
    {
        $productsIds = array_map(fn($line) => $line->productId, $request->lines);
        $products = $this->products->findByIds($productsIds);

        $order = $this->orderFactory->createFromRequest($request, $products);

        $paymentData = $this->paymentService->initiate(
            $order->getId(),
            $order->getClientEmail(),
            $order->getTotalAmount()
        );

        $order->assignPaymentDetails($paymentData['transactionId'], $paymentData['paymentUrl']);

        $this->orders->save($order);
        $this->uow->commit();
        $this->emailQueueService->queueOrderCreated($order->getId());

        return $this->orderMapper->toCreateOrderResponseDTO($order);
    }
}
