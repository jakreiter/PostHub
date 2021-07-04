<?php

namespace App\Repository;

use App\Entity\ScanPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ScanPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScanPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScanPlan[]    findAll()
 * @method ScanPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScanPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScanPlan::class);
    }

    // /**
    //  * @return ScanPlan[] Returns an array of ScanPlan objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ScanPlan
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
