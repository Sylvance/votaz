<?php

namespace App\Repository;

use App\Entity\Election;
use App\Entity\ElectionRegistration;
use App\Entity\Enum\RegistrationStatus;
use App\Entity\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ElectionRegistration>
 */
class ElectionRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectionRegistration::class);
    }

    public function findOneByElectionAndVoter(Election $election, Voter $voter): ?ElectionRegistration
    {
        return $this->findOneBy(['election' => $election, 'voter' => $voter]);
    }

    public function findOneByReceiptNumber(string $receiptNumber): ?ElectionRegistration
    {
        return $this->findOneBy(['receiptNumber' => $receiptNumber]);
    }

    /**
     * @return ElectionRegistration[]
     */
    public function findByElection(Election $election, ?RegistrationStatus $status = null, ?string $term = null): array
    {
        $qb = $this->createQueryBuilder('er')
            ->leftJoin('er.voter', 'v')
            ->where('er.election = :election')
            ->setParameter('election', $election);

        if (null !== $status) {
            $qb->andWhere('er.status = :status')->setParameter('status', $status);
        }

        if (null !== $term && '' !== $term) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'v.firstName LIKE :term',
                    'v.lastName LIKE :term',
                    'v.nationalId LIKE :term',
                    'er.receiptNumber LIKE :term',
                )
            )->setParameter('term', '%'.$term.'%');
        }

        return $qb->orderBy('er.createdAt', 'DESC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();
    }

    public function countForElection(Election $election, ?RegistrationStatus $status = null): int
    {
        $qb = $this->createQueryBuilder('er')
            ->select('COUNT(er.id)')
            ->where('er.election = :election')
            ->setParameter('election', $election);

        if (null !== $status) {
            $qb->andWhere('er.status = :status')->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}