<?php

namespace App\Repository;

use App\Entity\AgentAssignment;
use App\Entity\ObservationReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ObservationReport>
 */
class ObservationReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObservationReport::class);
    }

    /**
     * @return ObservationReport[]
     */
    public function findByAssignment(AgentAssignment $assignment): array
    {
        return $this->findBy(['assignment' => $assignment], ['submittedAt' => 'DESC']);
    }

    public function countForAssignment(AgentAssignment $assignment): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.assignment = :assignment')
            ->setParameter('assignment', $assignment)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return ObservationReport[]
     */
    public function findRecentWithIrregularities(int $limit = 20): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.irregularities = :true')
            ->setParameter('true', true)
            ->orderBy('r.submittedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
