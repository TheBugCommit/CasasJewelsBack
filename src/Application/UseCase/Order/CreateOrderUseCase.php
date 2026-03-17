<?php

namespace App\Application\UseCase\Order;

use App\Application\Exception\ProductNotFoundException;
use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Service\EmailServiceInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Infrastructure\Api\Mapper\OrderMapper;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;

final class CreateOrderUseCase
{
    public function __construct(
        private readonly OrderMapper $orderMapper,
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly EmailServiceInterface $emailService,
    ) {}

    public function execute(CreateOrderRequestDTO $request): CreateOrderResponseDTO
    {
        $productsIds = array_map(fn($line) => $line->productId, $request->lines);
        $products = $this->products->findByIds($productsIds);

        $transactionId = "123";
        $paymentUrl = "http://1234";

        $orderId = $this->uuidGenerator->generate();
        $order = Order::create(
            id:            $orderId,
            clientName:    $request->clientName,
            clientEmail:   $request->clientEmail,
            transactionId: $transactionId,
            paymentUrl:    $paymentUrl,
        );

        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }

        foreach ($request->lines as $lineDTO) {
            $product = $productsById[$lineDTO->productId] ?? null;
            if (!$product) {
                throw ProductNotFoundException::withId($lineDTO->productId);
            }

            $order->addLine(
                lineId:    $this->uuidGenerator->generate(),
                productId: $product->getId(),
                quantity:  $lineDTO->quantity,
                unitPrice: $product->getPrice()
            );
        }

        $this->orders->save($order); #TODO: Queue Batch Insert
        $this->emailService->sendOrderCreated($order);

        return $this->orderMapper->toCreateOrderResponseDTO($order);
    }
}
