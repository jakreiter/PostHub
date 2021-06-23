<?php

namespace App\Repository;

use App\Entity\User;
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


    public function findByFragment($fragment)
    {
        $results1 = $this->createQueryBuilder('o')
            ->andWhere('o.email LIKE :fragment OR o.lastName LIKE :fragment')
            ->setParameter('fragment', $fragment.'%')
            ->orderBy('o.email', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
        $results2 = $this->createQueryBuilder('o')
            ->andWhere('o.firstName LIKE :fragment OR o.email LIKE :fragment OR o.lastName LIKE :fragment')
            ->setParameter('fragment', '%'.$fragment.'%')
            ->orderBy('o.email', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        foreach ($results1 as $one) {
            /**
             * @var User $one
             */
            $ids1[$one->getId()] = $one->getId();
        }
        $mergedResults = $results1;
        foreach ($results2 as $one) {
            if (!isset($ids1[$one->getId()])) $mergedResults[]=$one;
        }
        return $mergedResults;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
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
