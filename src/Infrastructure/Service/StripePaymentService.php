<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\PaymentServiceInterface;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;

final class StripePaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $successUrl,
        private readonly string $cancelUrl,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * @return array{transactionId: string, paymentUrl: string}
     * @throws ApiErrorException
     */
    public function initiate(string $orderId, string $clientEmail, float $amount): array
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email'       => $clientEmail,
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) round($amount * 100),
                    'product_data' => [
                        'name' => 'Comanda #' . $orderId,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'metadata'    => ['order_id' => $orderId],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => $orderId,
                ],
            ],
            'success_url' => $this->successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $this->cancelUrl . '?order_id=' . $orderId,
        ]);

        if (!$session->id || !$session->url) {
            throw new RuntimeException('Stripe did not return a valid session.');
        }

        return [
            'transactionId' => $session->id,
            'paymentUrl'    => $session->url,
        ];
    }

    /** @throws ApiErrorException */
    public function refund(string $transactionId): void
    {
        $session = Session::retrieve($transactionId);

        $paymentIntentId = $session->payment_intent;
        if (!$paymentIntentId) {
            throw new RuntimeException(
                "No PaymentIntent found on Stripe session '{$transactionId}'. Cannot refund."
            );
        }

        Refund::create(['payment_intent' => $paymentIntentId]);
    }
}
