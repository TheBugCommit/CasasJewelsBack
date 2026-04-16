<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Order\ConfirmOrderRefundedUseCase;
use App\Application\UseCase\Order\MarkOrderAsPaidUseCase;
use App\Application\UseCase\Order\RetryPaymentUseCase;
use Psr\Log\LoggerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentWebhookController extends AbstractController
{
    public const CHECKOUT_COMPLETED = 'checkout.session.completed';
    public const REFUNDED = 'charge.refunded';
    public function __construct(
        private readonly string              $webhookSecret,
        private readonly RetryPaymentUseCase $retryPayment,
        private readonly MarkOrderAsPaidUseCase $markOrderAsPaid,
        private readonly ConfirmOrderRefundedUseCase $confirmOrderRefunded,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/webhook', name: 'payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
        } catch (SignatureVerificationException) {
            return $this->json(['error' => 'Invalid signature'], Response::HTTP_BAD_REQUEST);
        }

        switch ($event->type) {
            case self::CHECKOUT_COMPLETED:
                $session = $event->data->object;
                $orderId = $session->metadata->order_id ?? null;
                if ($orderId) {
                    $this->markOrderAsPaid->execute($orderId);

                }
                break;

            case self::REFUNDED:
                $charge = $event->data->object;
                $orderId = $charge->metadata->order_id ?? null;
                $this->logger->info("Comanda refund: $orderId");
                if ($orderId) {
                    $this->confirmOrderRefunded->execute($orderId);
                }
                break;
        }

        return $this->json(['status' => 'ok']);
    }

    #[Route('/retry/{orderId}', name: 'payment_retry', methods: ['POST'])]
    public function retry(string $orderId): JsonResponse
    {
        try {
            $result = $this->retryPayment->execute($orderId);
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
