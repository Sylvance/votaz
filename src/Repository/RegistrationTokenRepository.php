<?php

namespace App\Repository;

use App\Entity\Enum\TokenType;
use App\Entity\RegistrationToken;
use App\Entity\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegistrationToken>
 */
class RegistrationTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationToken::class);
    }

    /**
     * @return RegistrationToken[]
     */
    public function findActive(Voter $voter, TokenType $type): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.voter = :voter')
            ->andWhere('t.type = :type')
            ->andWhere('t.usedAt IS NULL')
            ->andWhere('t.expiresAt >= :now')
            ->setParameters([
                'voter' => $voter,
                'type' => $type,
                'now' => new \DateTimeImmutable(),
            ])
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}