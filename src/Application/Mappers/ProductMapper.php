<?php

namespace App\Application\Mappers;

use App\Domain\Entity\Product;
use Sce\CasasJewelsVendor\DTO\Entity\ProductDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateProduct\CreateProductResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\DeleteProduct\DeleteProductResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateProduct\UpdateProductResponseDTO;

final class ProductMapper
{
    /** @param Product[] $products */
    public function toProductListResponseDTO(
        array                    $products,
        int                      $total,
        GetProductListRequestDTO $request,
    ): GetProductListResponseDTO {
        return new GetProductListResponseDTO(
            items:      array_map($this->toProductDTO(...), $products),
            total:      $total,
            offset:     $request->offset,
            limit:      $request->limit,
            totalPages: (int) ceil($total / $request->limit),
        );
    }

    public function toProductResponseDTO(Product $product): GetProductDetailResponseDTO
    {
        return new GetProductDetailResponseDTO(
            data: $this->toProductDTO($product),
        );
    }

    public function toCreateProductResponseDTO(Product $product): CreateProductResponseDTO
    {
        return new CreateProductResponseDTO(
            data: $this->toProductDTO($product),
        );
    }

    public function toUpdateProductResponseDTO(Product $product): UpdateProductResponseDTO
    {
        return new UpdateProductResponseDTO(
            data: $this->toProductDTO($product),
        );
    }

    public function toDeleteProductResponseDTO(Product $product): DeleteProductResponseDTO
    {
        return new DeleteProductResponseDTO(
            id: $product->getId(),
        );
    }

    private function toProductDTO(Product $product): ProductDTO
    {
        return new ProductDTO(
            id:          $product->getId(),
            name:        $product->getName(),
            price:       $product->getPrice(),
            description: $product->getDescription(),
            category:    $product->getCategory()->value,
            imageUrl:    $product->getImageUrl(),
        );
    }
}
