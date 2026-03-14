<?php

namespace App\Application\UseCase\Order;

use App\Domain\Repository\OrderRepositoryInterface;
use App\Infrastructure\Api\Mapper\OrderMapper;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListResponseDTO;

class GetOrderListUseCase
{

    public function __construct(
        private OrderMapper $mapper,
        private OrderRepositoryInterface $orderRepository,
    )
    {}

    public function execute(GetOrderListRequestDTO $request): GetOrderListResponseDTO
    {
        $paginatedList = $this->orderRepository->findPaginated($request->limit, $request->offset);

        return $this->mapper->toOrderListResponseDTO(
            $paginatedList->items,
            $paginatedList->totalItems,
            $request
        );
    }
}
