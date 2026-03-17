<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Order;

interface EmailServiceInterface
{
    public function sendOrderCreated(Order $order): void;

    public function sendOrderDelivered(Order $order): void;
}
