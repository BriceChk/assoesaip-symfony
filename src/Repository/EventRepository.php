<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param integer $page The requested page
     * @param integer $nbItems Number of events per page (optional, default = 10)
     * @param string $campus Campus of the events (optional, default = all campuses)
     * @return Event[] Returns an array of Event objects
     */

    public function findByPage($page, $nbItems = 10, $campus = 'A')
    {
        // TODO: Rajouter la verif du campus avec le projet associé
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = :val')
            ->setParameter('val', true)
            //->andWhere('a.campus LIKE :cam')
            //->setParameter('cam', '%'.$campus.'%')
            ->orderBy('a.datePublished', 'DESC')
            ->setFirstResult($nbItems * ($page - 1))
            ->setMaxResults($nbItems)
            ->getQuery()
            ->getResult()
            ;
    }

    public function search($value): array
    {
        return $this->createQueryBuilder('p')
            ->where("p.published = 1")
            ->andWhere("p.title LIKE :val OR p.abstract LIKE :val")
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('p.datePublished', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
