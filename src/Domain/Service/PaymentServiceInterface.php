<?php

namespace App\Domain\Service;

interface PaymentServiceInterface
{
    public function initiate(string $productId, string $clientEmail, float $amount): array;
}
