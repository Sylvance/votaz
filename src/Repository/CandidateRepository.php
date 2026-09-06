<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Election;
use App\Entity\Enum\CandidateStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function findByElection(Election $election, ?CandidateStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.party', 'p')
            ->where('c.election = :election')
            ->setParameter('election', $election);

        if (null !== $status) {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        return $qb->orderBy('c.ballotPosition', 'ASC')
            ->addOrderBy('c.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countVotesFor(Candidate $candidate): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(v.id)')
            ->join('c.election', 'e')
            ->leftJoin('e.votes', 'v', 'WITH', 'v.candidate = c')
            ->where('c.id = :candidate')
            ->setParameter('candidate', $candidate->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}