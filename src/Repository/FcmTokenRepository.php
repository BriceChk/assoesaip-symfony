<?php

namespace App\Repository;

use App\Entity\FcmToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FcmToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method FcmToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method FcmToken[]    findAll()
 * @method FcmToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FcmTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FcmToken::class);
    }

    // /**
    //  * @return FcmToken[] Returns an array of FcmToken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FcmToken
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
