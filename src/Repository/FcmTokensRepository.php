<?php

namespace App\Repository;

use App\Entity\FcmTokens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FcmTokens|null find($id, $lockMode = null, $lockVersion = null)
 * @method FcmTokens|null findOneBy(array $criteria, array $orderBy = null)
 * @method FcmTokens[]    findAll()
 * @method FcmTokens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FcmTokensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FcmTokens::class);
    }

    // /**
    //  * @return FcmTokens[] Returns an array of FcmTokens objects
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
    public function findOneBySomeField($value): ?FcmTokens
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
