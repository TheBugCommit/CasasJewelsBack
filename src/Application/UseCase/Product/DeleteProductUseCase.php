<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Application\Exception\ProductNotFoundException;
use App\Application\Mappers\ProductMapper;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use Sce\CasasJewelsVendor\DTO\Requests\DeleteProduct\DeleteProductRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\DeleteProduct\DeleteProductResponseDTO;

final class DeleteProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $productMapper,
        private readonly UnitOfWorkInterface        $uow,
    ) {}

    public function execute(DeleteProductRequestDTO $request): DeleteProductResponseDTO
    {
        $product = $this->products->findById($request->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($request->id);
        }

        $this->products->delete($product);
        $this->uow->commit();

        return $this->productMapper->toDeleteProductResponseDTO($product);
    }
}
