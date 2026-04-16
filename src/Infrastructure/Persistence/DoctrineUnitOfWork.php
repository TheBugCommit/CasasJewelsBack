<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\UnitOfWorkInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUnitOfWork implements UnitOfWorkInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function commit(): void
    {
        $this->em->flush();
    }
}
