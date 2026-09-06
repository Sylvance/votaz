<?php

namespace App\Repository;

use App\Entity\Enum\PartyStatus;
use App\Entity\PoliticalParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PoliticalParty>
 */
class PoliticalPartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PoliticalParty::class);
    }

    /**
     * @return PoliticalParty[]
     */
    public function findByStatus(PartyStatus $status = PartyStatus::APPROVED): array
    {
        return $this->findBy(['status' => $status], ['leaderAnnouncedAt' => 'ASC']);
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.status', 'COUNT(p.id) AS total')
            ->groupBy('p.status')
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if ($status instanceof PartyStatus) {
                $status = $status->value;
            }
            $out[$status] = (int) $row['total'];
        }

        return $out;
    }
}