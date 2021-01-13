<?php

namespace App\Repository;

use App\Entity\CafetItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CafetItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CafetItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CafetItem[]    findAll()
 * @method CafetItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CafetItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CafetItem::class);
    }

    // /**
    //  * @return CafetItem[] Returns an array of CafetItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CafetItem
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
