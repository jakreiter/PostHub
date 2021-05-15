<?php

namespace App\Repository;

use App\Entity\LetterStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LetterStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method LetterStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method LetterStatus[]    findAll()
 * @method LetterStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LetterStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LetterStatus::class);
    }

    // /**
    //  * @return LetterStatus[] Returns an array of LetterStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LetterStatus
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
