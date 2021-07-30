<?php

namespace App\Service;

use App\Entity\Letter;
use App\Repository\LetterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailNotificationService
{
    private $em;
    private $doctrine;
    private $environment;
    private $output;
    private $letterRepository;

    public function __construct(EntityManagerInterface $em, $environment, ManagerRegistry $doctrine, ParameterBagInterface $parameterBag, LetterRepository $letterRepository)
    {
        $this->doctrine = $doctrine;
        $this->letterRepository = $letterRepository;
        $this->em = $em;
        $this->environment = $environment;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getNumberOfRequiringNotificationPerOrganization()
    {
        $em = $this->em;


        $query = $em->createQuery('SELECT organization.id,
                                            COUNT(letter.id) AS letters,                                           
                                            MAX(letter.lastAttemptToSendNotification) AS lastAttemptToSendNotification                                           
                                            FROM App\Entity\Letter letter
                                            LEFT JOIN letter.organization organization
                                            INDEX BY organization.id 
                                            WHERE    
                                             letter.notificationSent = false
                                            GROUP BY organization.id
                                            HAVING letters>0')

            ;
        // ->setParameter('lastAttemptToSendNotification', $maxLastAttemptToSendNotification)
        // $maxLastAttemptToSendNotification = new \DateTime('-1 hour');
        // AND ( letter.lastAttemptToSendNotification IS NULL OR letter.lastAttemptToSendNotification <= :lastAttemptToSendNotification)
        return $query->getResult();
    }



}