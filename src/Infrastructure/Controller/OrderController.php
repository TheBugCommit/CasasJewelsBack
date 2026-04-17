<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Order\CreateOrderUseCase;
use App\Application\UseCase\Order\RetryPaymentUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\RetryPayment\RetryPaymentRequestDTO;
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
        private readonly RetryPaymentUseCase $retryPaymentUseCase,
    ) {}

    #[Route('', methods: ['PUT','POST'])]
    public function create(#[MapRequestPayload] CreateOrderRequestDTO $request): JsonResponse
    {
        $response = $this->createOrder->execute($request);
        $jsonResponse = $this->serializer->serialize($response, 'json');

        return JsonResponse::fromJsonString($jsonResponse, Response::HTTP_CREATED);
    }

    #[Route('/retry/{orderId}', name: 'payment_retry', methods: ['POST'])]
    public function retry(string $orderId): JsonResponse
    {
        try {
            $result = $this->retryPaymentUseCase->execute(new RetryPaymentRequestDTO($orderId));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No s\'ha pogut reiniciar el pagament.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'transactionId' => $result->transactionId,
            'paymentUrl'    => $result->paymentUrl,
        ]);
    }
}
