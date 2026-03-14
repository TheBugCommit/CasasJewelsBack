<?php

namespace App\Infrastructure\Api\Mapper;

use App\Domain\Entity\Order;
use Sce\CasasJewelsVendor\DTO\Entity\OrderDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;

final class OrderMapper
{
    public function toOrderDTO(Order $order): OrderDTO
    {
        return new OrderDTO(
            id:         $order->getId(),
            clientName: $order->getClientName(),
            status:     $order->getStatus()->value,
            createdAt:  $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toCreateOrderResponseDTO(Order $order): CreateOrderResponseDTO
    {
        return new CreateOrderResponseDTO(
            $order->getTransactionId(),
            $order->getPaymentUrl()
        );
    }
}
