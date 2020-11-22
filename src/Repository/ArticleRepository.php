<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @param integer $page The requested page
     * @param integer $nbItems Number of articles per page (optional, default = 10)
     * @param string $campus Campus of the articles (optional, default = all campuses)
     * @return Article[] Returns an array of Article objects
     */

    public function findByPage($page, $nbItems = 10, $campus = 'A')
    {
        //TODO Rajouter la verification du campus avec le projet associé
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = :val')
            ->setParameter('val', true)
            //->andWhere('a.campus LIKE :cam')
            //->setParameter('cam', '%'.$campus.'%')
            ->orderBy('a.datePublished', 'DESC')
            ->setFirstResult($nbItems * ($page - 1))
            ->setMaxResults($nbItems)
            ->getQuery()
            ->getResult()
        ;
    }

    public function search($value): array
    {
        return $this->createQueryBuilder('p')
            ->where("p.published = 1")
            ->andWhere("p.title LIKE :val")
            ->orWhere("p.abstract LIKE :val")
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('p.datePublished', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
