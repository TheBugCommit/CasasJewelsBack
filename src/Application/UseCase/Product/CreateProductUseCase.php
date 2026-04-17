<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\ProductCategory;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly UnitOfWorkInterface        $uow,
        private readonly UuidGeneratorInterface     $uuidGenerator,
    ) {}

    public function execute(
        string $name,
        float $price,
        string $description,
        string $category,
        ?string $imageUrl = null,
    ): Product {
        $product = Product::create(
            id:          $this->uuidGenerator->generate(),
            name:        $name,
            price:       $price,
            description: $description,
            category:    ProductCategory::from($category),
            imageUrl:    $imageUrl,
        );

        $this->products->save($product);
        $this->uow->commit();

        return $product;
    }
}
