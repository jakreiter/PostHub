<?php

namespace App\Repository;

use App\Entity\Letter;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Letter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Letter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Letter[]    findAll()
 * @method Letter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Letter::class);
    }

    public function getNumberOfUnseenPerOrganization(Organization $organization)
    {
        $cnt = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.organization = :organization')->setParameter('organization', $organization)
            ->andWhere('a.seen = false')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        if (!$cnt) $cnt = 0;
        return (int)$cnt;
    }



    public function findRequiringNotificationForOrganization(Organization $organization)
    {

        $queryBuilder = $this->createQueryBuilder('letter')
            ->andWhere('letter.notificationSent = false')
            ->andWhere('letter.organization = :organization')
            ->setParameter('organization', $organization)
            ->addOrderBy('letter.lastAttemptToSendNotification', 'ASC');
        return $queryBuilder->getQuery()->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Letter
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
