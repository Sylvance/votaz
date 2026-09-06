<?php
namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('article')
            ->where('article.title LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->getQuery()
            ->getResult();
    }
}
