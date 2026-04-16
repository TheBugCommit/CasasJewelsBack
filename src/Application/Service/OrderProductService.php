<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Order;
use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;

final readonly class OrderProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @return array<string, Product>
     */
    public function getProductsForOrder(Order $order): array
    {
        $productIds = [];
        foreach ($order->getLines() as $line) {
            $productIds[] = $line->getProductId();
        }

        if (empty($productIds)) {
            return [];
        }

        $productsFromDb = $this->productRepository->findByIds(array_unique($productIds));

        $productsMap = [];
        foreach ($productsFromDb as $product) {
            $productsMap[$product->getId()] = $product;
        }

        return $productsMap;
    }
}
