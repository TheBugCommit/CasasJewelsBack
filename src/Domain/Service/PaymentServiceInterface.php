<?php

namespace App\Domain\Service;

interface PaymentServiceInterface
{
    public function initiate(string $orderId, string $clientEmail, float $amount): array;

    public function refund(string $transactionId): void;
}
