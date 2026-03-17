<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Admin;

use App\Application\UseCase\Order\GetOrderListUseCase;
use App\Application\UseCase\Order\UpdateOrderStatusUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/admin/orders')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private readonly GetOrderListUseCase      $getOrderList,
        private readonly SerializerInterface      $serializer,
        private readonly UpdateOrderStatusUseCase $updateOrderStatus,
    )
    {
    }

    #[Route('', methods: ['GET'])]
    public function index(#[MapQueryString] GetOrderListRequestDTO $request): JsonResponse
    {
        $orders = $this->getOrderList->execute($request);
        $jsonOrders = $this->serializer->serialize($orders, 'json');

        return JsonResponse::fromJsonString($jsonOrders);
    }

    #[Route('/status', methods: ['PATCH'])]
    public function updateStatus(#[MapRequestPayload] UpdateOrderStatusRequestDTO $request): JsonResponse
    {
        $order = $this->updateOrderStatus->execute($request);
        $jsonOrder = $this->serializer->serialize($order, 'json');
        return JsonResponse::fromJsonString($jsonOrder);
    }
}

