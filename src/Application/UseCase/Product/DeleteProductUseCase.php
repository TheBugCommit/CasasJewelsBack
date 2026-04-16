<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use DomainException;

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
            throw new DomainException("Producte amb ID {$id} no trobat.");
        }

        $this->products->delete($product);
        $this->uow->commit();
    }
}
