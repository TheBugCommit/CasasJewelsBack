<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Order\ConfirmOrderRefundedUseCase;
use App\Application\UseCase\Order\MarkOrderAsPaidUseCase;
use Psr\Log\LoggerInterface;
use Sce\CasasJewelsVendor\DTO\Requests\ConfirmOrderRefunded\ConfirmOrderRefundedRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\MarkOrderAsPaid\MarkOrderAsPaidRequestDTO;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class StripeWebhookController extends AbstractController
{
    public const CHECKOUT_COMPLETED = 'checkout.session.completed';
    public const REFUNDED           = 'charge.refunded';

    public function __construct(
        private readonly string                      $webhookSecret,
        private readonly MarkOrderAsPaidUseCase      $markOrderAsPaid,
        private readonly ConfirmOrderRefundedUseCase $confirmOrderRefunded,
        private readonly LoggerInterface             $logger,
    ) {}

    #[Route('/webhook', name: 'payment_webhook', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
        } catch (SignatureVerificationException $e) {
            $this->logger->error("Intent de webhook fraudulent o invàlid: " . $e->getMessage());
            return $this->json(['error' => 'Invalid signature'], Response::HTTP_BAD_REQUEST);
        }

        try {
            match ($event->type) {
                self::CHECKOUT_COMPLETED => $this->handleCheckoutCompleted($event->data->object),
                self::REFUNDED           => $this->handleRefunded($event->data->object),
                default                  => $this->logger->info("Event de Stripe ignorat: {$event->type}"),
            };
        } catch (\Exception $e) {
            $this->logger->critical("Error processant el webhook de Stripe: " . $e->getMessage());
            return $this->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['status' => 'ok']);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) {
            $this->logger->critical("Stripe Checkout Completed sense order_id al metadata! Session ID: {$session->id}");
            return;
        }

        $this->markOrderAsPaid->execute(new MarkOrderAsPaidRequestDTO($orderId));
        $this->logger->info("Comanda pagada amb èxit via webhook: $orderId");
    }

    private function handleRefunded(object $charge): void
    {
        $orderId = $charge->metadata->order_id ?? null;

        if (!$orderId) {
            $this->logger->critical("Stripe Charge Refunded sense order_id al metadata! Charge ID: {$charge->id}");
            return;
        }

        $this->confirmOrderRefunded->execute(new ConfirmOrderRefundedRequestDTO($orderId));
        $this->logger->info("Comanda reemborsada amb èxit via webhook: $orderId");
    }
}
