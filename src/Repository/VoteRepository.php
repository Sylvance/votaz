<?php

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function countForElection(Election $election): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Tallies: [candidateId => count] for an approved candidate in the election.
     */
    public function tally(Election $election): array
    {
        return $this->createQueryBuilder('v')
            ->select('IDENTITY(v.candidate) AS candidate_id', 'COUNT(v.id) AS total')
            ->where('v.election = :election')
            ->setParameter('election', $election)
            ->groupBy('v.candidate')
            ->getQuery()
            ->getResult();
    }
}