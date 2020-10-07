<?php

namespace App\Repository;

use App\Entity\AENews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AENews|null find($id, $lockMode = null, $lockVersion = null)
 * @method AENews|null findOneBy(array $criteria, array $orderBy = null)
 * @method AENews[]    findAll()
 * @method AENews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AENewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AENews::class);
    }

    // /**
    //  * @return AENews[] Returns an array of AENews objects
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
    public function findOneBySomeField($value): ?AENews
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
