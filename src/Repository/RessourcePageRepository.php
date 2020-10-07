<?php

namespace App\Repository;

use App\Entity\RessourcePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RessourcePage|null find($id, $lockMode = null, $lockVersion = null)
 * @method RessourcePage|null findOneBy(array $criteria, array $orderBy = null)
 * @method RessourcePage[]    findAll()
 * @method RessourcePage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RessourcePageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RessourcePage::class);
    }

    // /**
    //  * @return RessourcePage[] Returns an array of RessourcePage objects
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
    public function findOneBySomeField($value): ?RessourcePage
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
