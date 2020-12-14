<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\UploadedImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UploadedImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method UploadedImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method UploadedImage[]    findAll()
 * @method UploadedImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadedImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadedImage::class);
    }

    /**
     * @return UploadedImage[] Returns an array of UploadedImage objects
     */
    public function findAllOrphanImagesByProject(Project $value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.project = :val')
            ->andWhere('u.article IS NULL')
            ->andWhere('u.event IS NULL')
            ->andWhere('u.ressourcePage IS NULL')
            ->setParameter('val', $value->getId())
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?UploadedImage
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
