<?php

declare(strict_types=1);

namespace App\Application\UseCase\Order;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Mappers\OrderMapper;
use App\Application\Service\OrderProductService;
use App\Domain\Repository\OrderRepositoryInterface;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderDetailResponseDTO;

final class GetOrderDetailUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderProductService      $orderProductService,
        private readonly OrderMapper              $orderMapper,
    ) {}

    public function execute(GetOrderDetailRequestDTO $request): GetOrderDetailResponseDTO
    {
        $order = $this->orderRepository->findById($request->orderId);
        if (!$order) {
            throw OrderNotFoundException::withId($request->orderId);
        }

        $productsMap = $this->orderProductService->getProductsForOrder($order);

        return $this->orderMapper->toGetOrderDetailResponseDTO($order, $productsMap);
    }
}
