<?php

namespace App\Domain\Entity;

class OrderLine
{
    private string $id;
    private Order  $order;
    private string $productId;
    private int    $quantity;
    private float  $unitPrice;

    public function __construct(
        string $id,
        Order  $order,
        string $productId,
        int    $quantity,
        float  $unitPrice
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantitat ha de ser més gran que 0.');
        }
        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('El preu no pot ser negatiu.');
        }

        $this->id        = $id;
        $this->order     = $order;
        $this->productId = $productId;
        $this->quantity  = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function getId(): string        { return $this->id; }
    public function getOrder(): Order      { return $this->order; }
    public function getProductId(): string { return $this->productId; }
    public function getQuantity(): int     { return $this->quantity; }
    public function getUnitPrice(): float  { return $this->unitPrice; }

    public function getTotal(): float      { return $this->quantity * $this->unitPrice; }
}
