<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\OrderStatus;
use DateTimeImmutable;

class Order
{
    private string $id;
    private string $clientName;
    private string $clientEmail;
    private OrderStatus $status;
    private DateTimeImmutable $createdAt;
    private string $transactionId;
    private string $paymentUrl;

    /** @var OrderLine[] */
    private iterable $lines;

    public function __construct(
        string            $id,
        string            $clientName,
        string            $clientEmail,
        OrderStatus       $status,
        DateTimeImmutable $createdAt,
    )
    {
        $this->id = $id;
        $this->clientName = $clientName;
        $this->clientEmail = $clientEmail;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->lines = [];
    }

    public static function create(
        string $id,
        string $clientName,
        string $clientEmail,
    ): self
    {
        return new self(
            id: $id,
            clientName: $clientName,
            clientEmail: $clientEmail,
            status: OrderStatus::PendingPayment,
            createdAt: new DateTimeImmutable(),
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function addLine(string $lineId, string $productId, int $quantity, float $unitPrice): void
    {
        $this->lines[] = new OrderLine($lineId, $this, $productId, $quantity, $unitPrice);
    }

    public function getTotalAmount(): float
    {
        $total = 0.0;
        foreach ($this->lines as $line) {
            $total += $line->getTotal();
        }
        return $total;
    }

    /** @return OrderLine[] */
    public function getLines(): array
    {
        return $this->lines instanceof \Traversable
            ? iterator_to_array($this->lines)
            : $this->lines;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTransactionId(): string|null
    {
        return $this->transactionId;
    }

    public function getPaymentUrl(): string|null
    {
        return $this->paymentUrl;
    }

    public function transitionTo(OrderStatus $newStatus): void
    {
        $this->status->assertCanTransitionTo($newStatus);
        $this->status = $newStatus;
    }

    public function assignPaymentDetails(string $transactionId, string $paymentUrl): void
    {
        $this->transactionId = $transactionId;
        $this->paymentUrl = $paymentUrl;
    }
}
