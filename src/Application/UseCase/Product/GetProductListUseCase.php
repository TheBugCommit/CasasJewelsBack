<?php

namespace App\Application\UseCase\Product;

use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Api\Mapper\ProductMapper;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListResponseDTO;

final class GetProductListUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductMapper              $mapper,
    ) {}

    public function execute(GetProductListRequestDTO $request): GetProductListResponseDTO
    {
        $ids      = $this->products->findIdsPaginated($request->limit, $request->offset);
        $products = $this->products->findByIds($ids);
        $total    = $this->products->countAll();

        return $this->mapper->toProductListResponseDTO($products, $total, $request);
    }
}
