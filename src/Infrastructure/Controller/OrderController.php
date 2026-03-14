<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Order\CreateOrderUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly CreateOrderUseCase  $createOrder,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateOrderRequestDTO $request): JsonResponse
    {
        $response = $this->createOrder->execute($request);
        $jsonResponse = $this->serializer->serialize($response, 'json');

        return JsonResponse::fromJsonString($jsonResponse, Response::HTTP_CREATED);
    }
}
