<?php

namespace App\Repository;

use App\Entity\AgentAssignment;
use App\Entity\Election;
use App\Entity\Enum\AssignmentStatus;
use App\Entity\PartyAgent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgentAssignment>
 */
class AgentAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgentAssignment::class);
    }

    /**
     * @return AgentAssignment[]
     */
    public function findByElection(Election $election, ?AssignmentStatus $status = null): array
    {
        $criteria = ['election' => $election];
        if (null !== $status) {
            $criteria['status'] = $status;
        }

        return $this->findBy($criteria, ['assignedAt' => 'DESC']);
    }

    /**
     * @return AgentAssignment[]
     */
    public function findByAgent(PartyAgent $agent, ?AssignmentStatus $status = null): array
    {
        $criteria = ['agent' => $agent];
        if (null !== $status) {
            $criteria['status'] = $status;
        }

        return $this->findBy($criteria, ['assignedAt' => 'DESC']);
    }

    public function findOneByAgentAndElection(PartyAgent $agent, Election $election): ?AgentAssignment
    {
        return $this->findOneBy(['agent' => $agent, 'election' => $election]);
    }
}
