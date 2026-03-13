<?php

namespace App\DataFixtures;


use App\Domain\Entity\AdminUser;
use App\Domain\ValueObject\UserRole;
use App\Infrastructure\Security\AuthUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class AdminUserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $uuid = Uuid::v4()->toRfc4122();

        $tempUser = new AuthUser($uuid, 'Admin', 'admin@casasjewels.com', '', UserRole::ADMIN);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $tempUser,
            'admin'
        );

        $admin = new AdminUser(
            $uuid,
            'Admin',
            'admin@casasjewels.com',
            $hashedPassword,
        );

        $manager->persist($admin);
        $manager->flush();
    }
}
