<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Backoffice;

use App\Application\Exception\ProductNotFoundException;
use App\Application\UseCase\Product\CreateProductUseCase;
use App\Application\UseCase\Product\DeleteProductUseCase;
use App\Application\UseCase\Product\GetProductListUseCase;
use App\Application\UseCase\Product\UpdateProductUseCase;
use App\Domain\ValueObject\ProductCategory;
use App\Infrastructure\Service\ProductImageUploadService;
use Sce\CasasJewelsVendor\DTO\Requests\CreateProduct\CreateProductRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\DeleteProduct\DeleteProductRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\UpdateProduct\UpdateProductRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/products')]
class BackofficeProductController extends AbstractController
{
    private const CSRF_DELETE = 'delete_product_';

    public function __construct(
        private readonly GetProductListUseCase     $getProductList,
        private readonly CreateProductUseCase      $createProduct,
        private readonly UpdateProductUseCase      $updateProduct,
        private readonly DeleteProductUseCase      $deleteProduct,
        private readonly ProductImageUploadService $imageUpload,
        private readonly ValidatorInterface        $validator,
    ) {}

    #[Route('', name: 'admin_products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit  = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 1);

        $response = $this->getProductList->execute(new GetProductListRequestDTO($limit, $offset));

        return $this->render('admin/products/index.html.twig', [
            'products'     => $response->items,
            'total'        => $response->total,
            'totalPages'   => $response->totalPages,
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

        try {
            $dto = new CreateProductRequestDTO(
                name:            trim($request->request->getString('name')),
                price:           (float) $request->request->get('price'),
                description:     trim($request->request->getString('description')),
                category:        $request->request->getString('category'),
                preparationDays: $request->request->getInt('preparation_days'),
                shippingDays:    $request->request->getInt('shipping_days'),
                imageUrl:        $this->uploadImageIfPresent($request),
            );

            if (!$this->validateDto($dto)) {
                return $this->redirectToRoute('admin_products');
            }

            $response = $this->createProduct->execute($dto);
            $this->addFlash('success', "Producte '{$response->data->name}' creat correctament.");
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_products');
    }

    #[Route('/{id}/edit', name: 'admin_product_edit', methods: ['POST'])]
    public function edit(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('edit_product_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Petició no vàlida (CSRF Token incorrecte).');
            return $this->redirectToRoute('admin_products');
        }

        try {
            $dto = new UpdateProductRequestDTO(
                id:              $id,
                name:            trim($request->request->getString('name')),
                price:           (float) $request->request->get('price'),
                description:     trim($request->request->getString('description')),
                category:        $request->request->getString('category'),
                preparationDays: $request->request->getInt('preparation_days'),
                shippingDays:    $request->request->getInt('shipping_days'),
                imageUrl:        $this->uploadImageIfPresent($request),
            );

            if (!$this->validateDto($dto)) {
                return $this->redirectToRoute('admin_products');
            }

            $response = $this->updateProduct->execute($dto);
            $this->addFlash('success', "Producte '{$response->data->name}' actualitzat correctament.");
        } catch (ProductNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception) {
            $this->addFlash('error', 'Error en actualitzar el producte.');
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
            $this->deleteProduct->execute(new DeleteProductRequestDTO($id));
            $this->addFlash('success', 'Producte eliminat correctament.');
        } catch (ProductNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception) {
            $this->addFlash('error', 'Error en eliminar el producte.');
        }

        return $this->redirectToRoute('admin_products');
    }

    private function uploadImageIfPresent(Request $request): ?string
    {
        $imageFile = $request->files->get('image');
        return $imageFile !== null ? $this->imageUpload->upload($imageFile) : null;
    }

    private function validateDto(object $dto): bool
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return false;
        }

        return true;
    }
}
