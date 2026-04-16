<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Order;
use App\Domain\Model\PaginatedOrderList;
use App\Domain\Repository\OrderRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;

final class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function save(Order $order): void
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    public function findById(string $id): Order|null
    {
        return $this->em->getRepository(Order::class)->find($id);
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function findPaginated(int $limit, int $offset): PaginatedOrderList
    {
        $query = $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($offset - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, false);

        return new PaginatedOrderList(
            items: iterator_to_array($paginator->getIterator()),
            totalItems: $paginator->count()
        );
    }
}
