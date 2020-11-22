<?php

namespace App\Repository;

use App\Entity\AssoEsaipSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssoEsaipSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssoEsaipSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssoEsaipSettings[]    findAll()
 * @method AssoEsaipSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssoEsaipSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssoEsaipSettings::class);
    }

    public function getRoombooksRecipients(): array
    {
        /** @var AssoEsaipSettings $setting */
        try {
            $setting = $this->createQueryBuilder('a')
                ->andWhere('a.name = :val')
                ->setParameter('val', 'roombooks_recipients')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return array();
        }

        return explode(' ', $setting->getValue());
    }

    public function isMaintenanceModeEnabled(): bool
    {
        /** @var AssoEsaipSettings $setting */
        try {
            $setting = $this->createQueryBuilder('a')
                ->andWhere('a.name = :val')
                ->setParameter('val', 'maintenance_mode_enabled')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return false;
        }

        return $setting->getValue() == '1';
    }

    public function getMaintenanceModeMessage(): string
    {
        /** @var AssoEsaipSettings $setting */
        try {
            $setting = $this->createQueryBuilder('a')
                ->andWhere('a.name = :val')
                ->setParameter('val', 'maintenance_mode_message')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return '';
        }

        return $setting->getValue();
    }



    // /**
    //  * @return AssoEsaipSettings[] Returns an array of AssoEsaipSettings objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AssoEsaipSettings
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
