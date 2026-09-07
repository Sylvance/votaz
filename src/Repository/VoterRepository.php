<?php

namespace App\Repository;

use App\Entity\Enum\VoterStatus;
use App\Entity\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voter>
 *
 * @method Voter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Voter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Voter[]    findAll()
 * @method Voter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voter::class);
    }

    public function findOneByNationalId(string $nationalId): ?Voter
    {
        return $this->findOneBy(['nationalId' => $nationalId]);
    }

    public function findOneByVoterNumber(string $voterNumber): ?Voter
    {
        return $this->findOneBy(['voterNumber' => $voterNumber]);
    }

    public function findOneByUsernameOrEmail(string $username, ?string $email = null): ?Voter
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.username = :username')
            ->setParameter('username', $username);

        if (null !== $email && '' !== $email) {
            $qb->orWhere('v.email = :email')
                ->setParameter('email', $email);
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Voter[]
     */
    public function search(?string $term = null, ?VoterStatus $status = null, array $order = ['id' => 'DESC'], int $limit = 500): array
    {
        $qb = $this->createQueryBuilder('v');

        if (null !== $term && '' !== $term) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'v.firstName LIKE :term',
                    'v.lastName LIKE :term',
                    'v.nationalId LIKE :term',
                    'v.voterNumber LIKE :term',
                    'v.email LIKE :term',
                    'v.confirmationCode LIKE :term',
                    'v.username LIKE :term',
                )
            )->setParameter('term', '%'.$term.'%');
        }

        if (null !== $status) {
            $qb->andWhere('v.status = :status')->setParameter('status', $status);
        }

        foreach ($order as $field => $dir) {
            $qb->addOrderBy('v.'.$field, $dir);
        }

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countConfirmed(): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.status = :status')
            ->setParameter('status', VoterStatus::CONFIRMED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('v.status', 'COUNT(v.id) AS total')
            ->groupBy('v.status')
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if ($status instanceof VoterStatus) {
                $status = $status->value;
            }
            $out[$status] = (int) $row['total'];
        }

        return $out;
    }
}
