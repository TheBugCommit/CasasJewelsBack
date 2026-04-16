<?php

namespace App\Application\UseCase\Order;

use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;

final class GetOrderDetailUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Retorna un array amb la comanda i el mapa de productes, o null si no existeix.
     * @return array{order: \App\Domain\Entity\Order, products: array<string, \App\Domain\Entity\Product>}|null
     */
    public function execute(string $orderId): ?array
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            return null;
        }

        $productIds = [];
        foreach ($order->getLines() as $line) {
            $productIds[] = $line->getProductId();
        }

        $productsFromDb = $this->productRepository->findByIds(array_unique($productIds));
        $productsMap = [];
        foreach ($productsFromDb as $product) {
            $productsMap[$product->getId()] = $product;
        }

        return [
            'order'    => $order,
            'products' => $productsMap,
        ];
    }
}
