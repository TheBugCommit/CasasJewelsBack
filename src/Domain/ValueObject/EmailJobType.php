<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum EmailJobType: string
{
    case OrderCreated    = 'order_created';
    case OrderDelivering = 'order_delivering';
    case OrderDelivered  = 'order_delivered';
}
