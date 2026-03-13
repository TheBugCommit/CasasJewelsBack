<?php

namespace App\Application\UseCase\Product;

use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Api\Mapper\ProductMapper;
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
        return $this->mapper->toProductResponseDTO($product);
    }
}
