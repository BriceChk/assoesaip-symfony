<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventOccurrence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventOccurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventOccurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventOccurrence[]    findAll()
 * @method EventOccurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventOccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventOccurrence::class);
    }

    /**
     * @param Event $event
     * @return EventOccurrence[] Returns an array of EventOccurrence objects
     */
    public function findNextEventOccurrences($event)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.event = :val')
            ->setParameter('val', $event)
            ->andWhere('e.date >= :now')
            ->setParameter('now', date('Y-m-d'))
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $start
     * @param $end
     * @return EventOccurrence[] Returns an array of EventOccurrence objects
     */
    public function findBetweenDates($start, $end)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.date >= :start')
            ->setParameter('start', $start)
            ->andWhere('e.date <= :end')
            ->setParameter('end', $end)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?EventOccurrence
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
