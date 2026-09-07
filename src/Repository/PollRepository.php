<?php

namespace App\Repository;

use App\Entity\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poll>
 */
class PollRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }

    /**
     * @return Poll[]
     */
    public function findOpenAndPast(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.endsAt IS NULL OR p.endsAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.endsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPast(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.endsAt IS NOT NULL AND p.endsAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.endsAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
