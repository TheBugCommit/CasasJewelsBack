<?php

namespace App\Application\UseCase\Order;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Api\Mapper\OrderMapper;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;
use Symfony\Component\Uid\Uuid;

final class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderMapper $orderMapper,
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function execute(CreateOrderRequestDTO $request): CreateOrderResponseDTO
    {
        $product = $this->products->findById($request->productId);

        $transactionId = "123";
        $paymentUrl = "http://1234";

        $order = Order::create(
            id:            Uuid::v4()->toRfc4122(),
            productId:     $product->getId(),
            clientName:    $request->clientName,
            clientEmail:   $request->clientEmail,
            transactionId: $transactionId,
            paymentUrl:    $paymentUrl,
        );

        $this->orders->save($order); #TODO: Queue Batch Insert

        return $this->orderMapper->toCreateOrderResponseDTO($order);
    }
}
