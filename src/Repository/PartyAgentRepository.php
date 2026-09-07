<?php

namespace App\Repository;

use App\Entity\Enum\PartyAgentStatus;
use App\Entity\PartyAgent;
use App\Entity\PoliticalParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartyAgent>
 */
class PartyAgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartyAgent::class);
    }

    /**
     * @return PartyAgent[]
     */
    public function findByParty(PoliticalParty $party, ?PartyAgentStatus $status = null): array
    {
        $criteria = ['party' => $party];
        if (null !== $status) {
            $criteria['status'] = $status;
        }

        return $this->findBy($criteria, ['lastName' => 'ASC']);
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('a.status', 'COUNT(a.id) AS total')
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $status = $row['status'];
            if ($status instanceof PartyAgentStatus) {
                $status = $status->value;
            }
            $out[$status] = (int) $row['total'];
        }

        return $out;
    }

    public function findOneByCode(string $agentCode): ?PartyAgent
    {
        return $this->findOneBy(['agentCode' => $agentCode]);
    }
}
