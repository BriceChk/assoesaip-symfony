<?php

namespace App\Repository;

use App\Entity\RoomBook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoomBook|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomBook|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomBook[]    findAll()
 * @method RoomBook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomBookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomBook::class);
    }

    // /**
    //  * @return RoomBook[] Returns an array of RoomBook objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RoomBook
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
