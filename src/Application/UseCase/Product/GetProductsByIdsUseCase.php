<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Application\Mappers\ProductMapper;
use App\Domain\Repository\ProductRepositoryInterface;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductsByIdsRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductsByIdsResponseDTO;

final class GetProductsByIdsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $mapper,
    ) {}

    public function execute(GetProductsByIdsRequestDTO $request): GetProductsByIdsResponseDTO
    {
        $products = $this->products->findByIds($request->ids);

        return $this->mapper->toProductsByIdsResponseDTO($products);
    }
}
