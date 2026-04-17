<?php

declare(strict_types=1);

namespace App\Application\UseCase\Product;

use App\Domain\Entity\Product;
use App\Application\Exception\ProductNotFoundException;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\UnitOfWorkInterface;
use App\Domain\ValueObject\ProductCategory;
use App\Infrastructure\Service\ProductImageUploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly UnitOfWorkInterface        $uow,
        private readonly ProductImageUploadService  $imageUpload,
    ) {}

    public function execute(
        string $id,
        string $name,
        float $price,
        string $description,
        string $category,
        ?UploadedFile $image = null,
    ): Product {
        $product = $this->products->findById($id);

        if ($product === null) {
            throw ProductNotFoundException::withId($id);
        }

        $product->setName($name);
        $product->setPrice($price);
        $product->setDescription($description);
        $product->setCategory(ProductCategory::from($category));
        if ($image !== null) {
            if ($product->getImageUrl() !== null) {
                $this->imageUpload->delete($product->getImageUrl());
            }
            $product->setImageUrl($this->imageUpload->upload($image));
        }

        $this->products->save($product);
        $this->uow->commit();

        return $product;
    }
}
