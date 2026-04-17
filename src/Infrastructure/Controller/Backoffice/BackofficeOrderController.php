<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Backoffice;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\Order\GetOrderDetailUseCase;
use App\Application\UseCase\Order\GetOrderListUseCase;
use App\Application\UseCase\Order\RefundOrderUseCase;
use App\Application\UseCase\Order\UpdateOrderStatusUseCase;
use App\Domain\ValueObject\OrderStatus;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryOrder\GetOrderListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\RefundOrder\RefundOrderRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateOrder\UpdateOrderStatusRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/orders')]
class BackofficeOrderController extends AbstractController
{
    private const CSRF_PREFIX = 'update_status_';

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

    public function __construct(
        private readonly GetOrderListUseCase      $getOrderList,
        private readonly UpdateOrderStatusUseCase $updateOrderStatus,
        private readonly RefundOrderUseCase       $refundOrder,
        private readonly GetOrderDetailUseCase    $getOrderDetail,
        private readonly ValidatorInterface       $validator,
    ) {}

    #[Route('', name: 'home', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit  = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);

        $response    = $this->getOrderList->execute(new GetOrderListRequestDTO($limit, $offset));
        $transitions = [];
        foreach (OrderStatus::cases() as $status) {
            $transitions[$status->value] = array_map(
                fn($next) => $next->value,
                $status->allowedTransitions()
            );
        }

        return $this->render('/admin/orders/index.html.twig', [
            'orders'       => $response->items,
            'total'        => $response->total,
            'totalPages'   => $response->totalPages,
            'currentLimit' => $limit,
            'currentPage'  => $offset,
            'transitions'  => $transitions,
            'statusLabels' => self::STATUS_LABELS,
            'actionLabels' => self::ACTION_LABELS,
            'buttonColors' => self::BUTTON_COLORS,
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(string $id, string $status, Request $request): Response
    {
        if (!$this->isCsrfTokenValid(self::CSRF_PREFIX . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('home');
        }

        try {
            if ($status === OrderStatus::RefundPending->value) {
                $dto = new RefundOrderRequestDTO($id);
                $this->validateDtoOrFail($dto);
                $this->refundOrder->execute($dto);
                $this->addFlash('success', 'S\'ha iniciat el procés per cancel·lar la comanda i reemborsar el pagament.');
            } else {
                $dto = new UpdateOrderStatusRequestDTO($id, $status);
                $this->validateDtoOrFail($dto);
                $this->updateOrderStatus->execute($dto);
                $this->addFlash('success', 'L\'estat de la comanda s\'ha actualitzat a: ' . $status);
            }
        } catch (OrderNotFoundException|\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception) {
            $this->addFlash('error', 'S\'ha produït un error en actualitzar la comanda.');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/{id}', name: 'admin_order_detail', methods: ['GET'])]
    public function detail(string $id): Response
    {
        try {
            $dto      = new GetOrderDetailRequestDTO($id);
            $this->validateDtoOrFail($dto);
            $response = $this->getOrderDetail->execute($dto);
        } catch (OrderNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        } catch (\Exception) {
            $this->addFlash('error', 'S\'ha produït un error en carregar la comanda.');
            return $this->redirectToRoute('home');
        }

        return $this->render('admin/orders/detail.html.twig', [
            'order' => $response->order,
        ]);
    }

    private function validateDtoOrFail(object $dto): void
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $messages = array_map(fn($e) => $e->getMessage(), iterator_to_array($errors));
            throw new \InvalidArgumentException(implode(', ', $messages));
        }
    }
}
