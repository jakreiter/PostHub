<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }


    public function findByFragment($fragment)
    {
        $results1 = $this->createQueryBuilder('o')
            ->andWhere('o.name LIKE :val')
            ->setParameter('val', $fragment.'%')
            ->orderBy('o.name', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
        $results2 = $this->createQueryBuilder('o')
            ->andWhere('o.name LIKE :val')
            ->setParameter('val', '%'.$fragment.'%')
            ->orderBy('o.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        foreach ($results1 as $one) {
            /**
             * @var Organization $one
             */
            $ids1[$one->getId()] = $one->getId();
        }
        $megedResults = $results1;
        foreach ($results2 as $one) {
            if (!isset($ids1[$one->getId()])) $megedResults[]=$one;
        }
        return $megedResults;
    }


    /*
    public function findOneBySomeField($value): ?Organization
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
