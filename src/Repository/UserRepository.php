<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[] Returns an array of User objects
     */

    public function search($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere("p.firstName LIKE :val")
            ->orWhere("p.lastName LIKE :val")
            ->orWhere("p.username LIKE :val")
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('p.firstName', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    // They have set their promo & campus
    public function getValidUsersCount() {
        return $this->createQueryBuilder('u')
            ->where("u.campus != ''")
            ->andWhere("u.promo != ''")
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalUsersCount() {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLastWeekUsersCount() {
        $date = (new DateTime('now'))->sub(new \DateInterval('P7D'));

        return $this->createQueryBuilder('u')
            ->where("u.lastLogin >= :date")
            ->setParameter(':date', $date)
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
    * @return User[] Returns an array of User objects
    */
    public function findByRole($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.roles LIKE :val')
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    // /**
    //  * @return ProjectMember[] Returns an array of User objects
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
    public function findOneBySomeField($value): ?User
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