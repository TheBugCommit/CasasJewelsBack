<?php
declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Application\Exception\ProductNotFoundException;
use App\Application\Mappers\ProductMapper;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\ProductCategory;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateProduct\UpdateProductRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateProduct\UpdateProductResponseDTO;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $productMapper,
        private readonly UnitOfWorkInterface        $uow,
    ) {}

    public function execute(UpdateProductRequestDTO $request): UpdateProductResponseDTO
    {
        $product = $this->products->findById($request->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($request->id);
        }

        $product->updateDetails(
            $request->name,
            $request->price,
            $request->description,
            ProductCategory::from($request->category),
            $request->preparationDays,
            $request->shippingDays,
        );

        if ($request->imageUrl !== null) {
            $product->updateImage($request->imageUrl);
        }

        $this->products->save($product);
        $this->uow->commit();

        return $this->productMapper->toUpdateProductResponseDTO($product);
    }
}
