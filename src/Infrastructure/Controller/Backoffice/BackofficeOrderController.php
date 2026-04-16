<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Backoffice;

use App\Application\UseCase\Order\GetOrderDetailUseCase;
use App\Application\UseCase\Order\RefundOrderUseCase;
use App\Application\UseCase\Order\GetOrderListUseCase;
use App\Application\UseCase\Order\UpdateOrderStatusUseCase;
use App\Domain\ValueObject\OrderStatus;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/orders')]
class BackofficeOrderController extends AbstractController
{
    private const CSRF_PREFIX = 'update_status_';
    public function __construct(
        private readonly GetOrderListUseCase      $getOrderList,
        private readonly UpdateOrderStatusUseCase $updateOrderStatus,
        private readonly RefundOrderUseCase       $refundOrder,
        private readonly GetOrderDetailUseCase $getOrderDetail
    ) {}


    #[Route('', name: 'home', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);

        $requestDTO = new GetOrderListRequestDTO($limit, $offset);
        $responseDTO = $this->getOrderList->execute($requestDTO);

        $transitions = [];
        foreach (OrderStatus::cases() as $status) {
            $transitions[$status->value] = array_map(
                fn($nextStatus) => $nextStatus->value,
                $status->allowedTransitions()
            );
        }

        $statusLabels = [
            OrderStatus::PendingPayment->value => 'Pendent de Pagament',
            OrderStatus::Paid->value => 'Pagat',
            OrderStatus::Processing->value => 'Processant',
            OrderStatus::Delivering->value => 'Enviant',
            OrderStatus::Delivered->value  => 'Lliurat',
            OrderStatus::Cancelled->value  => 'Cancel·lada',
            OrderStatus::RefundPending->value  => 'Reemborsament en curs...',
        ];

        $actionLabels = [
            OrderStatus::Processing->value     => 'Començar a processar',
            OrderStatus::Paid->value           => 'Marcar com a Pagat (Manual)',
            OrderStatus::Delivering->value     => 'Enviar comanda',
            OrderStatus::Delivered->value      => 'Marcar com lliurada',
            OrderStatus::RefundPending->value  => 'Cancel·lar i Reemborsar',
            OrderStatus::Cancelled->value      => 'Forçar Cancel·lació',
        ];

        $buttonColors = [
            OrderStatus::PendingPayment->value => 'btn-outline-secondary',
            OrderStatus::Paid->value => 'btn-outline-warning',
            OrderStatus::Processing->value     => 'btn-outline-info',
            OrderStatus::Delivering->value => 'btn-outline-primary',
            OrderStatus::Delivered->value  => 'btn-outline-success',
            OrderStatus::RefundPending->value  => 'btn-outline-danger',
            OrderStatus::Cancelled->value  => 'btn-outline-dark',
        ];

        return $this->render('/admin/orders/index.html.twig', [
            'orders'      => $responseDTO->items,
            'total'       => $responseDTO->total,
            'totalPages'  => $responseDTO->totalPages,
            'currentLimit' => $limit,
            'currentPage' => $offset,
            'transitions'  => $transitions,
            'statusLabels' => $statusLabels,
            'actionLabels' => $actionLabels,
            'buttonColors' => $buttonColors
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(string $id, string $status, Request $request): Response
    {
        $expectedToken = self::CSRF_PREFIX . $id;
        if (!$this->isCsrfTokenValid($expectedToken, $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('home');
        }

        try {
            if ($status === OrderStatus::RefundPending->value) {
                $this->refundOrder->execute($id);
                $this->addFlash('success', 'S\'ha iniciat el proces per cancel·lar la comanda i reembolsar el pagament.');
            } else {
                $requestDTO = new UpdateOrderStatusRequestDTO($id, $status);
                $this->updateOrderStatus->execute($requestDTO);
                $this->addFlash('success', 'L\'estat de la comanda s\'ha actualitzat a: ' . $status);
            }
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'S\'ha produït un error en actualitzar la comanda.');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/{id}', name: 'admin_order_detail', methods: ['GET'])]
    public function detail(string $id): Response
    {
        $viewData = $this->getOrderDetail->execute($id);

        if (!$viewData) {
            $this->addFlash('error', 'Comanda no trobada.');
            return $this->redirectToRoute('home');
        }

        return $this->render('admin/orders/detail.html.twig', $viewData);
    }
}

