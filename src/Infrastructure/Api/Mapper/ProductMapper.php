<?php

namespace App\Infrastructure\Api\Mapper;

use App\Domain\Entity\Product;
use Sce\CasasJewelsVendor\DTO\Entity\ProductDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListResponseDTO;

final class ProductMapper
{
    /** @param Product[] $products */
    public function toProductListDTO(
        array                $products,
        int                  $total,
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

    private function toProductDTO(Product $product): ProductDTO
    {
        return new ProductDTO(
            id:          $product->getId(),
            name:        $product->getName(),
            price:       $product->getPrice(),
            description: $product->getDescription(),
        );
    }
}
