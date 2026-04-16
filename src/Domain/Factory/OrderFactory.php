<?php

namespace App\Domain\Factory;

use App\Application\Exception\ProductNotFoundException;
use App\Domain\Entity\Order;
use App\Domain\Entity\Product;
use App\Domain\Service\UuidGeneratorInterface;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;

final class OrderFactory
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator
    ) {}

    /**
     * @param Product[] $products
     */
    public function createFromRequest(CreateOrderRequestDTO $request, array $products): Order
    {
        $orderId = $this->uuidGenerator->generate();
        $order = Order::create(
            id:          $orderId,
            clientName:  $request->clientName,
            clientEmail: $request->clientEmail
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

        return $order;
    }
}
