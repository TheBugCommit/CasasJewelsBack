<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\ProductCategory;

class Product
{
    private string $id;
    private string $name;
    private float $price;
    private string $description;
    private ProductCategory $category;
    private ?string $imageUrl;

    public function __construct(
        string $id,
        string $name,
        float $price,
        string $description,
        ProductCategory $category,
        ?string $imageUrl = null,
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->price       = $price;
        $this->description = $description;
        $this->category    = $category;
        $this->imageUrl    = $imageUrl;
    }

    public static function create(
        string $id,
        string $name,
        float $price,
        string $description,
        ProductCategory $category,
        ?string $imageUrl = null,
    ): self {
        return new self($id, $name, $price, $description, $category, $imageUrl);
    }

    public function updateDetails(
        string $name,
        float $price,
        string $description,
        ProductCategory $category
    ): void {
        if ($price <= 0) {
            throw new \DomainException("El preu d'un producte ha de ser positiu.");
        }

        if (trim($name) === '') {
            throw new \DomainException("El nom no pot estar buit.");
        }

        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->category = $category;
    }

    public function updateImage(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ProductCategory
    {
        return $this->category;
    }

    public function setCategory(ProductCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }
}
