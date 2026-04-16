<?php

namespace App\Domain\ValueObject;

use DomainException;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid           = 'paid';
    case Processing = 'processing';
    case Delivering = 'delivering';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';
    case RefundPending  = 'refound_pending';

    /** @return OrderStatus[] */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PendingPayment => [self::Paid],
            self::Paid           => [self::RefundPending, self::Processing],
            self::Processing => [self::Delivering, self::RefundPending],
            self::Delivering => [self::Delivered],
            self::RefundPending => [self::Cancelled],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function assertCanTransitionTo(self $next): void
    {
        if (!in_array($next, $this->allowedTransitions(), true)) {
            throw new DomainException(
                "Cannot transition order from '{$this->value}' to '{$next->value}'"
            );
        }
    }
}
