<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Backoffice;

use App\Application\UseCase\Product\CreateProductUseCase;
use App\Application\UseCase\Product\DeleteProductUseCase;
use App\Application\UseCase\Product\GetProductListUseCase;
use App\Domain\ValueObject\ProductCategory;
use App\Infrastructure\Service\ProductImageUploadService;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/products')]
class BackofficeProductController extends AbstractController
{
    private const CSRF_DELETE = 'delete_product_';

    public function __construct(
        private readonly GetProductListUseCase      $getProductList,
        private readonly CreateProductUseCase       $createProduct,
        private readonly DeleteProductUseCase       $deleteProduct,
        private readonly ProductImageUploadService  $imageUpload,
    ) {}

    #[Route('', name: 'admin_products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit  = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);

        $responseDTO = $this->getProductList->execute(new GetProductListRequestDTO($limit, $offset));

        return $this->render('admin/products/index.html.twig', [
            'products'     => $responseDTO->items,
            'total'        => $responseDTO->total,
            'totalPages'   => $responseDTO->totalPages,
            'currentLimit' => $limit,
            'currentPage'  => $offset,
            'categories'   => ProductCategory::cases(),
        ]);
    }

    #[Route('/create', name: 'admin_product_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('create_product', $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('admin_products');
        }

        $name        = trim($request->request->getString('name'));
        $price       = $request->request->get('price');
        $description = trim($request->request->getString('description'));
        $category    = $request->request->getString('category');

        if (!$name || !$price || !$description || !$category) {
            $this->addFlash('error', 'Tots els camps són obligatoris.');
            return $this->redirectToRoute('admin_products');
        }

        try {
            ProductCategory::from($category);
        } catch (\ValueError) {
            $this->addFlash('error', 'Categoria no vàlida.');
            return $this->redirectToRoute('admin_products');
        }

        $imageUrl = null;
        $imageFile = $request->files->get('image');
        if ($imageFile !== null) {
            $imageUrl = $this->imageUpload->upload($imageFile);
        }

        try {
            $this->createProduct->execute(
                name:        $name,
                price:       (float) $price,
                description: $description,
                category:    $category,
                imageUrl:    $imageUrl,
            );
            $this->addFlash('success', "Producte '{$name}' creat correctament.");
        } catch (\Exception) {
            $this->addFlash('error', 'Error en crear el producte.');
        }

        return $this->redirectToRoute('admin_products');
    }

    #[Route('/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid(self::CSRF_DELETE . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('admin_products');
        }

        try {
            $this->deleteProduct->execute($id);
            $this->addFlash('success', 'Producte eliminat correctament.');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception) {
            $this->addFlash('error', 'Error en eliminar el producte.');
        }

        return $this->redirectToRoute('admin_products');
    }
}
