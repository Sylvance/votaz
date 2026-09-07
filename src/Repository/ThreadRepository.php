<?php

namespace App\Repository;

use App\Entity\Enum\ThreadCategory;
use App\Entity\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thread>
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findOneBySlug(string $slug): ?Thread
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return Thread[]
     */
    public function findLatest(?ThreadCategory $category = null, int $limit = 30): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.posts', 'p')
            ->leftJoin('t.election', 'e');

        if (null !== $category) {
            $qb->where('t.category = :category')->setParameter('category', $category);
        }

        $qb->addSelect('(SELECT COUNT(posts.id) FROM App\Entity\Post posts WHERE posts.thread = t.id) AS HIDDEN reply_count')
            ->setMaxResults($limit)
            ->orderBy('t.isPinned', 'DESC')
            ->addOrderBy('t.updatedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Thread[]
     */
    public function search(string $term, int $limit = 50): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.title LIKE :term OR t.content LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
