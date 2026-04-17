<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Application\Mappers\ProductMapper;
use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\ProductCategory;
use Sce\CasasJewelsVendor\DTO\Requests\CreateProduct\CreateProductRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateProduct\CreateProductResponseDTO;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $productMapper,
        private readonly UnitOfWorkInterface        $uow,
        private readonly UuidGeneratorInterface     $uuidGenerator,
    ) {}

    public function execute(CreateProductRequestDTO $request): CreateProductResponseDTO
    {
        $product = Product::create(
            id:          $this->uuidGenerator->generate(),
            name:        $request->name,
            price:       $request->price,
            description: $request->description,
            category:    ProductCategory::from($request->category),
            imageUrl:    $request->imageUrl,
        );

        $this->products->save($product);
        $this->uow->commit();

        return $this->productMapper->toCreateProductResponseDTO($product);
    }
}
