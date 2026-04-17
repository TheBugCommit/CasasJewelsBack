<?php

namespace App\Application\Mappers;

use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use App\Domain\Entity\Product;
use Sce\CasasJewelsVendor\DTO\Entity\OrderDetailDTO;
use Sce\CasasJewelsVendor\DTO\Entity\OrderDetailLineDTO;
use Sce\CasasJewelsVendor\DTO\Entity\OrderDTO;
use Sce\CasasJewelsVendor\DTO\Requests\ConfirmOrderRefunded\ConfirmOrderRefundedResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\CreateOrder\CreateOrderResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\MarkOrderAsPaid\MarkOrderAsPaidResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderDetailResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\RefundOrder\RefundOrderResponseDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusResponseDTO;

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

    public function toOrderListResponseDTO(array $items, int $totalItems, GetOrderListRequestDTO $request): GetOrderListResponseDTO
    {
        return new GetOrderListResponseDTO(
            items:      array_map(fn($item) => $this->toOrderDTO($item), $items),
            total:      $totalItems,
            offset:     $request->offset,
            limit:      $request->limit,
            totalPages: (int) ceil($totalItems / $request->limit),
        );
    }

    public function toUpdateOrderStatusResponseDTO(Order $order): UpdateOrderStatusResponseDTO
    {
        return new UpdateOrderStatusResponseDTO(
            orderId: $order->getId(),
            status:  $order->getStatus()->value,
        );
    }
    public function toMarkOrderAsPaidResponseDTO(Order $order): MarkOrderAsPaidResponseDTO
    {
        return new MarkOrderAsPaidResponseDTO(
            orderId: $order->getId(),
            status:  $order->getStatus()->value,
        );
    }

    public function toConfirmOrderRefundedResponseDTO(Order $order): ConfirmOrderRefundedResponseDTO
    {
        return new ConfirmOrderRefundedResponseDTO(
            orderId: $order->getId(),
            status:  $order->getStatus()->value,
        );
    }

    public function toRefundOrderResponseDTO(Order $order): RefundOrderResponseDTO
    {
        return new RefundOrderResponseDTO(
            orderId: $order->getId(),
            status:  $order->getStatus()->value,
        );
    }

    /** @param array<string, Product> $productsMap */
    public function toGetOrderDetailResponseDTO(Order $order, array $productsMap): GetOrderDetailResponseDTO
    {
        return new GetOrderDetailResponseDTO(
            order: $this->toOrderDetailDTO($order, $productsMap),
        );
    }

    /** @param array<string, Product> $productsMap */
    private function toOrderDetailDTO(Order $order, array $productsMap): OrderDetailDTO
    {
        $lines = array_map(
            fn(OrderLine $line) => $this->toOrderDetailLineDTO($line, $productsMap),
            $order->getLines()
        );

        return new OrderDetailDTO(
            id: $order->getId(),
            clientName: $order->getClientName(),
            clientEmail: $order->getClientEmail(),
            status: $order->getStatus()->value,
            createdAt: $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
            lines: $lines,
            totalAmount: $order->getTotalAmount(),
        );
    }

    /** @param array<string, Product> $productsMap */
    private function toOrderDetailLineDTO(OrderLine $line, array $productsMap): OrderDetailLineDTO
    {
        $product = $productsMap[$line->getProductId()] ?? null;

        return new OrderDetailLineDTO(
            id:              $line->getId(),
            productId:       $line->getProductId(),
            productName:     $product?->getName(),
            productImageUrl: $product?->getImageUrl(),
            quantity:        $line->getQuantity(),
            unitPrice:       $line->getUnitPrice(),
            total:           $line->getTotal(),
            productDeleted:  $product === null,
        );
    }
}
