<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\ProjectCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @param $count
     * @param string $campus
     * @return News[] Returns an array of News objects
     */
    public function findLatestNews($count, $campus = ""): array {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.project', 'p')
            ->andWhere('p.campus LIKE :val')
            ->setParameter('val', '%' . $campus . '%')
            ->orderBy('n.datePublished', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $count
     * @param string $campus
     * @return News[] Returns an array of News objects
     */
    public function findStarredNews($campus = "A"): array {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.project', 'p')
            ->andWhere('p.campus LIKE :val')
            ->andWhere('n.starred = 1')
            ->setParameter('val', '%' . $campus . '%')
            ->orderBy('n.datePublished', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }


    public function findCategoryNews(ProjectCategory $categ, $campus = "A"): array {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.project', 'p')
            ->andWhere('p.campus LIKE :val')
            ->andWhere('p.category = :categ')
            ->setParameter('val', '%' . $campus . '%')
            ->setParameter('categ', $categ)
            ->orderBy('n.datePublished', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return News[] Returns an array of News objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?News
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
