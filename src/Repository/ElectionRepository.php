<?php

namespace App\Repository;

use App\Entity\Election;
use App\Entity\Enum\ElectionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Election>
 */
class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    /**
     * @param ElectionStatus[] $statuses
     *
     * @return Election[]
     */
    public function findByStatuses(array $statuses, string $order = 'DESC'): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->orderBy('e.votingStartAt', $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Election[]
     */
    public function findUpcoming(): array
    {
        return $this->findBy(
            ['status' => [ElectionStatus::REGISTRATION_OPEN, ElectionStatus::NOMINATION]],
            ['votingStartAt' => 'ASC'],
            null,
            0,
        );
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('e.status', 'COUNT(e.id) AS total')
            ->groupBy('e.status')
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if ($status instanceof ElectionStatus) {
                $status = $status->value;
            }
            $out[$status] = (int) $row['total'];
        }

        return $out;
    }
}
