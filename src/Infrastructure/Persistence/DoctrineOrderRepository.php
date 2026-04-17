<?php

namespace App\Infrastructure\Persistence;

use App\Application\Model\PaginatedOrderList;
use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

final class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function save(Order $order): void
    {
        $this->em->persist($order);
    }

    public function delete(Order $order): void
    {
        $this->em->remove($order);
    }

    public function findById(string $id): Order|null
    {
        return $this->em->createQueryBuilder()
            ->select('o', 'l')
            ->from(Order::class, 'o')
            ->leftJoin('o.lines', 'l')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('o', 'l')
            ->from(Order::class, 'o')
            ->leftJoin('o.lines', 'l')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function findPaginated(int $limit, int $offset): PaginatedOrderList
    {
        $query = $this->em->createQueryBuilder()
            ->select('o', 'l')
            ->from(Order::class, 'o')
            ->leftJoin('o.lines', 'l')
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($offset - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, true);

        return new PaginatedOrderList(
            items: iterator_to_array($paginator->getIterator()),
            totalItems: $paginator->count()
        );
    }
}
