<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\AdminUser;
use App\Domain\Repository\AdminUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineAdminUserRepository implements AdminUserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findByEmail(string $email): ?AdminUser
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(AdminUser::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
