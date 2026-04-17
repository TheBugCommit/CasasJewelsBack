<?php
declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Domain\ValueObject\OrderStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class OrderStatusExtension extends AbstractExtension
{
    private const STATUS_LABELS = [
        OrderStatus::PendingPayment->value => 'Pendent de Pagament',
        OrderStatus::Paid->value           => 'Pagat',
        OrderStatus::Processing->value     => 'Processant',
        OrderStatus::Delivering->value     => 'Enviant',
        OrderStatus::Delivered->value      => 'Lliurat',
        OrderStatus::Cancelled->value      => 'Cancel·lada',
        OrderStatus::RefundPending->value  => 'Reemborsament en curs...',
    ];

    private const ACTION_LABELS = [
        OrderStatus::Processing->value    => 'Començar a processar',
        OrderStatus::Paid->value          => 'Marcar com a Pagat (Manual)',
        OrderStatus::Delivering->value    => 'Enviar comanda',
        OrderStatus::Delivered->value     => 'Marcar com lliurada',
        OrderStatus::RefundPending->value => 'Cancel·lar i Reemborsar',
        OrderStatus::Cancelled->value     => 'Forçar Cancel·lació',
    ];

    private const BUTTON_COLORS = [
        OrderStatus::PendingPayment->value => 'btn-outline-secondary',
        OrderStatus::Paid->value           => 'btn-outline-warning',
        OrderStatus::Processing->value     => 'btn-outline-info',
        OrderStatus::Delivering->value     => 'btn-outline-primary',
        OrderStatus::Delivered->value      => 'btn-outline-success',
        OrderStatus::RefundPending->value  => 'btn-outline-danger',
        OrderStatus::Cancelled->value      => 'btn-outline-dark',
    ];

    private const BADGE_COLORS = [
        OrderStatus::PendingPayment->value => 'bg-secondary',
        OrderStatus::Paid->value           => 'bg-warning text-dark',
        OrderStatus::Processing->value     => 'bg-info text-dark',
        OrderStatus::Delivering->value     => 'bg-primary',
        OrderStatus::Delivered->value      => 'bg-success',
        OrderStatus::Cancelled->value      => 'bg-danger',
        OrderStatus::RefundPending->value  => 'bg-warning text-dark',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('order_status_label', [$this, 'getStatusLabel']),
            new TwigFunction('order_action_label', [$this, 'getActionLabel']),
            new TwigFunction('order_button_color', [$this, 'getButtonColor']),
            new TwigFunction('order_allowed_transitions', [$this, 'getAllowedTransitions']),
            new TwigFunction('order_badge_color', [$this, 'getBadgeColor']),
            new TwigFunction('order_all_statuses', [$this, 'getAllStatuses']),
        ];
    }

    public function getStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    public function getActionLabel(string $status): string
    {
        return self::ACTION_LABELS[$status] ?? ucfirst($status);
    }

    public function getButtonColor(string $status): string
    {
        return self::BUTTON_COLORS[$status] ?? 'btn-outline-secondary';
    }

    public function getBadgeColor(string $status): string
    {
        return self::BADGE_COLORS[$status] ?? 'bg-secondary';
    }

    public function getAllStatuses(): array
    {
        return array_keys(self::STATUS_LABELS);
    }

    /**
     * @return string[]
     */
    public function getAllowedTransitions(string $statusValue): array
    {
        try {
            $status = OrderStatus::from($statusValue);
            return array_map(fn($next) => $next->value, $status->allowedTransitions());
        } catch (\ValueError) {
            return [];
        }
    }
}
