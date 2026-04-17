<?php

namespace App\Application\UseCase\Product;

use App\Application\Exception\ProductNotFoundException;
use App\Application\Mappers\ProductMapper;
use App\Domain\Repository\ProductRepositoryInterface;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailResponseDTO;

final class GetProductDetailUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $mapper,
    ) {}

    public function execute(GetProductDetailRequestDTO $request): GetProductDetailResponseDTO
    {
        $product = $this->products->findById($request->id);
        if(!$product) {
            throw ProductNotFoundException::withId($request->id);
        }

        return $this->mapper->toProductResponseDTO($product);
    }
}
