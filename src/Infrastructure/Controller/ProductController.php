<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Product\GetProductListUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly GetProductListUseCase $getProductList,
    ) {}

    #[Route('/api/products', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $request  = $this->serializer->deserialize($request->getContent(), GetProductListRequestDTO::class, 'json');
        $products = $this->getProductList->execute($request);
        $jsonProducts = $this->serializer->serialize($products, 'json');
        return $this->json($jsonProducts);
    }
}
