<?php

namespace App\Repository;

use App\Entity\ProjectPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectPage[]    findAll()
 * @method ProjectPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectPage::class);
    }

    // /**
    //  * @return ProjectPage[] Returns an array of ProjectPage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjectPage
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
