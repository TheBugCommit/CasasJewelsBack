<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\UseCase\Product\GetProductDetailUseCase;
use App\Application\UseCase\Product\GetProductListUseCase;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductDetailRequestDTO;
use Sce\CasasJewelsVendor\DTO\Requests\QueryProducts\GetProductListRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly GetProductListUseCase $getProductList,
        private readonly GetProductDetailUseCase $getProductDetail,
    ) {}

    #[Route('/api/products', methods: ['GET'])]
    public function index(#[MapQueryString] GetProductListRequestDTO $request): JsonResponse
    {
        $products = $this->getProductList->execute($request);
        $jsonProducts = $this->serializer->serialize($products, 'json');
        return JsonResponse::fromJsonString($jsonProducts);
    }

    #[Route('/api/product', methods: ['GET'])]
    public function get(#[MapQueryString] GetProductDetailRequestDTO $request): JsonResponse
    {
        $product = $this->getProductDetail->execute($request);
        $jsonProduct = $this->serializer->serialize($product, 'json');
        return JsonResponse::fromJsonString($jsonProduct);
    }

}
