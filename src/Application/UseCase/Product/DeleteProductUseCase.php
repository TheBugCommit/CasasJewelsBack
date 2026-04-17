<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Application\Exception\ProductNotFoundException;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;

final class DeleteProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly UnitOfWorkInterface        $uow,
    ) {}

    public function execute(string $id): void
    {
        $product = $this->products->findById($id);

        if ($product === null) {
            throw ProductNotFoundException::withId($id);
        }

        $this->products->delete($product);
        $this->uow->commit();
    }
}
