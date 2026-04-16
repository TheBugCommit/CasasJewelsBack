<?php

namespace App\DataFixtures;

use App\Domain\Entity\Product;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator
    ) {}

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $product = Product::create(
                $this->uuidGenerator->generate(),
                'Anell d\'Or Blanc amb Diamant #' . ($i + 1),
                1250.00 + ($i * 10),
                'Anell elegant d\'or blanc de 18k amb un diamant de 0.5 quirats.',
                ProductCategory::Ring
            );

            $manager->persist($product);
        }

        $manager->flush();
    }
}
