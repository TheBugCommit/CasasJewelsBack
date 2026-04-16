<?php

namespace App\Application\UseCase\Order;

use App\Application\Service\OrderProductService;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;

final class GetOrderDetailUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface   $orderRepository,
        private readonly OrderProductService $orderProductService,
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

        $productsMap = $this->orderProductService->getProductsForOrder($order);

        return [
            'order'    => $order,
            'products' => $productsMap,
        ];
    }
}
