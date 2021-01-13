<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use DateTime;
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
        //TODO Campus ???
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

    /**
     * @param string $campus
     * @return EventOccurrence[] Returns an array of EventOccurrence objects
     */
    public function findNext($campus = "A")
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.event', 'ev')
            ->innerJoin('ev.project', 'p')
            ->andWhere('e.date >= :start')
            ->andWhere('ev.published = :pub')
            ->andWhere('ev.private = :priv')
            ->andWhere('p.campus LIKE :camp')
            ->setParameter('start', new DateTime('now'))
            ->setParameter('pub', true)
            ->setParameter('priv', false)
            ->setParameter('camp', '%' . $campus . '%')
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param ProjectCategory $categ
     * @param string $campus
     * @return EventOccurrence[] Returns an array of EventOccurrence objects
     */
    public function findCategoryNext(ProjectCategory $categ, $campus = "A")
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.event', 'ev')
            ->innerJoin('ev.project', 'p')
            ->andWhere('e.date >= :start')
            ->andWhere('ev.published = :pub')
            ->andWhere('ev.private = :priv')
            ->andWhere('p.campus LIKE :camp')
            ->andWhere('p.category = :categ')
            ->setParameter('start', new DateTime('now'))
            ->setParameter('pub', true)
            ->setParameter('priv', false)
            ->setParameter('camp', '%' . $campus . '%')
            ->setParameter('categ', $categ)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param Project $project
     * @return EventOccurrence[] Returns an array of EventOccurrence objects
     */
    public function findProjectNext(Project $project)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.event', 'ev')
            ->andWhere('ev.project = :proj')
            ->andWhere('e.date >= :start')
            ->andWhere('ev.published = :pub')
            ->andWhere('ev.private = :priv')
            ->setParameter('proj', $project)
            ->setParameter('start', new DateTime('now'))
            ->setParameter('pub', true)
            ->setParameter('priv', false)
            ->orderBy('e.date', 'ASC')
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
