<?php

namespace App\Application\Service;


use App\Domain\Entity\Order;
use App\Domain\Service\PaymentServiceInterface;

final readonly class OrderPaymentService
{
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    public function generatePaymentForOrder(Order $order): void
    {
        $paymentData = $this->paymentService->initiate(
            $order->getId(),
            $order->getClientEmail(),
            $order->getTotalAmount()
        );

        $order->assignPaymentDetails($paymentData['transactionId'], $paymentData['paymentUrl']);
    }
}
