<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Admin;

use App\Application\UseCase\Order\GetOrderListUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/admin/orders')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private readonly GetOrderListUseCase    $getOrderList,
        private readonly SerializerInterface    $serializer,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(#[MapQueryString] GetOrderListRequestDTO $request): JsonResponse
    {
        $orders = $this->getOrderList->execute($request);
        $jsonOrders   = $this->serializer->serialize($orders, 'json');

        return JsonResponse::fromJsonString($jsonOrders);
    }
}

