<?php

namespace App\Repository;

use App\Entity\Enum\RoundTableStatus;
use App\Entity\RoundTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoundTable>
 */
class RoundTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoundTable::class);
    }

    /**
     * @return RoundTable[]
     */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.status IN (:statuses)')
            ->setParameter('statuses', [RoundTableStatus::SCHEDULED, RoundTableStatus::LIVE])
            ->orderBy('r.scheduledAt', 'ASC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();
    }
}