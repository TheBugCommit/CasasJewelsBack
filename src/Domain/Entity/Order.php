<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\OrderStatus;
use DateTimeImmutable;

class Order
{
    private string $id;
    private string $productId;
    private string $clientName;
    private string $clientEmail;
    private OrderStatus $status;
    private DateTimeImmutable $createdAt;
    private string $transactionId;
    private string $paymentUrl;

    public function __construct(
        string            $id,
        string            $productId,
        string            $clientName,
        string            $clientEmail,
        OrderStatus       $status,
        DateTimeImmutable $createdAt,
        string            $transactionId,
        string            $paymentUrl,
    ) {
        $this->id            = $id;
        $this->productId     = $productId;
        $this->clientName    = $clientName;
        $this->clientEmail   = $clientEmail;
        $this->status        = $status;
        $this->createdAt     = $createdAt;
        $this->transactionId = $transactionId;
        $this->paymentUrl    = $paymentUrl;
    }

    public static function create(
        string $id,
        string $productId,
        string $clientName,
        string $clientEmail,
        string $transactionId,
        string $paymentUrl,
    ): self {
        return new self(
            id:            $id,
            productId:     $productId,
            clientName:    $clientName,
            clientEmail:   $clientEmail,
            status:        OrderStatus::Processing,
            createdAt:     new DateTimeImmutable(),
            transactionId: $transactionId,
            paymentUrl:    $paymentUrl,
        );
    }

    public function getId(): string                    { return $this->id; }
    public function getProductId(): string             { return $this->productId; }
    public function getClientName(): string            { return $this->clientName; }
    public function getClientEmail(): string           { return $this->clientEmail; }
    public function getStatus(): OrderStatus           { return $this->status; }
    public function getCreatedAt(): DateTimeImmutable  { return $this->createdAt; }
    public function getTransactionId(): string         { return $this->transactionId; }
    public function getPaymentUrl(): string            { return $this->paymentUrl; }

    public function transitionTo(OrderStatus $newStatus): void
    {
        $this->status->assertCanTransitionTo($newStatus);
        $this->status = $newStatus;
    }
}
