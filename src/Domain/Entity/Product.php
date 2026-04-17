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
    private int $preparationDays;
    private int $shippingDays;

    public function __construct(
        string $id,
        string $name,
        float $price,
        string $description,
        ProductCategory $category,
        ?string $imageUrl = null,
        int $preparationDays = 0,
        int $shippingDays = 0,
    ) {
        $this->id              = $id;
        $this->name            = $name;
        $this->price           = $price;
        $this->description     = $description;
        $this->category        = $category;
        $this->imageUrl        = $imageUrl;
        $this->preparationDays = $preparationDays;
        $this->shippingDays    = $shippingDays;
    }

    public static function create(
        string $id,
        string $name,
        float $price,
        string $description,
        ProductCategory $category,
        ?string $imageUrl = null,
        int $preparationDays = 0,
        int $shippingDays = 0,
    ): self {
        return new self($id, $name, $price, $description, $category, $imageUrl, $preparationDays, $shippingDays);
    }

    public function updateDetails(
        string $name,
        float $price,
        string $description,
        ProductCategory $category,
        int $preparationDays,
        int $shippingDays,
    ): void {
        if ($price <= 0) {
            throw new \DomainException("El preu d'un producte ha de ser positiu.");
        }

        if (trim($name) === '') {
            throw new \DomainException("El nom no pot estar buit.");
        }

        $this->name            = $name;
        $this->price           = $price;
        $this->description     = $description;
        $this->category        = $category;
        $this->preparationDays = $preparationDays;
        $this->shippingDays    = $shippingDays;
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

    public function getPreparationDays(): int { return $this->preparationDays; }
    public function getShippingDays(): int    { return $this->shippingDays; }
}
