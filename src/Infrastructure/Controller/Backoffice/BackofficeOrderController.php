<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Backoffice;

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
    public function __construct(
        private readonly GetOrderListUseCase      $getOrderList,
        private readonly UpdateOrderStatusUseCase $updateOrderStatus,
    )
    {
    }

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
            OrderStatus::Processing->value => 'Processant',
            OrderStatus::Delivering->value => 'Enviant',
            OrderStatus::Delivered->value  => 'Lliurat',
        ];

        $buttonColors = [
            OrderStatus::Delivering->value => 'btn-outline-primary',
            OrderStatus::Delivered->value  => 'btn-outline-success',
        ];

        return $this->render('/admin/orders/index.html.twig', [
            'orders'      => $responseDTO->items,
            'total'       => $responseDTO->total,
            'totalPages'  => $responseDTO->totalPages,
            'currentLimit' => $limit,
            'currentPage' => $offset,
            'transitions'  => $transitions,
            'statusLabels' => $statusLabels,
            'buttonColors' => $buttonColors
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(string $id, string $status, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_status_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('home');
        }

        try {
            $requestDTO = new UpdateOrderStatusRequestDTO($id, $status);
            $this->updateOrderStatus->execute($requestDTO);

            $this->addFlash('success', 'L\'estat de la comanda s\'ha actualitzat a: ' . $status);
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'S\'ha produït un error en actualitzar la comanda.');
        }

        return $this->redirectToRoute('home');
    }
}

