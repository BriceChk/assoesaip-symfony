<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectMember[]    findAll()
 * @method ProjectMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMember::class);
    }

    // /**
    //  * @return ProjectMember[] Returns an array of ProjectMember objects
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

    /**
     * @param Project $project
     * @param User $user
     * @return ProjectMember|null
     */
    public function findOne($project, $user): ?ProjectMember
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.user = :val')
                ->andWhere('p.project = :val2')
                ->setParameter('val', $user)
                ->setParameter('val2', $project)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /*
    public function findOneBySomeField($value): ?ProjectMember
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
