<?php

namespace App\Repository;

use App\Entity\TopicResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TopicResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopicResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopicResponse[]    findAll()
 * @method TopicResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopicResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopicResponse::class);
    }

    // /**
    //  * @return TopicResponse[] Returns an array of TopicResponse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TopicResponse
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
