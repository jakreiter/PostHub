<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\ScanPlan;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\LetterStatus;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;


class AppFixtures extends Fixture
{


    private $encoder;
    private $projectDir;
    private $colorNames;

    public function __construct(UserPasswordEncoderInterface $encoder, ParameterBagInterface $parameterBag)
    {
        $this->encoder = $encoder;
        $this->projectDir = $parameterBag->get('project_dir');
        $this->colorNames = NiceNames::COLOR_NAMES;
    }

    public function load(ObjectManager $manager)
    {

        $letterStatus = new LetterStatus();
        $letterStatus->setName('In the office');
        $manager->persist($letterStatus);

        $letterStatus = new LetterStatus();
        $letterStatus->setName('Given to the recipient');
        $manager->persist($letterStatus);


        $letterStatus = new LetterStatus();
        $letterStatus->setName('In the office/scan');
        $manager->persist($letterStatus);


        $letterStatus = new LetterStatus();
        $letterStatus->setName('Sent by traditional mail');
        $manager->persist($letterStatus);



        $scanPlans = [];

        $scanPlans[1] = new ScanPlan();
        $scanPlans[1] ->setName('ScanNo');
        $manager->persist($scanPlans[1]);

        $scanPlans[2] = new ScanPlan();
        $scanPlans[2] ->setName('ScanNoPaid');
        $manager->persist($scanPlans[2]);

        $scanPlans[3] = new ScanPlan();
        $scanPlans[3] ->setName('ScanYesPaid');
        $scanPlans[3]->setScan(true);
        $manager->persist($scanPlans[3]);

        $scanPlans[4] = new ScanPlan();
        $scanPlans[4] ->setName('ScanYesAll');
        $scanPlans[4]->setScan(true);
        $manager->persist($scanPlans[4]);



        $users = [];
        $user = new User();
        $email = $_ENV['ADMIN_EMAIL_INITIAL'];
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setEnabled(true);
        $plainPassword = $_ENV['ADMIN_PASS_INITIAL'];
        $encoded = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $manager->persist($user);
        $users[] = $user;

        for ($i=0; $i<111; $i++) {
            $user = new User();
            $rand_key = array_rand(NiceNames::POPULAR_NAMES);
            $firstName = NiceNames::POPULAR_NAMES[$rand_key];

            $rand_key = array_rand(NiceNames::POPULAR_SURNAMES);
            $lastName = NiceNames::POPULAR_SURNAMES[$rand_key];

            $username = $firstName.'.'.$lastName.'.'.rand(2,99);

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $email = $username.'@'.$_ENV['MAIL_DOMAIN_FOR_FIXURES'];
            $user->setEmail( $email );
            $user->setEnabled(true);
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }


        $locations = [];

        $location = new Location();
        $location->setName('New York');
        $location->setStreet('Btjetbn');
        $manager->persist($location);
        $locations[] = $location;

        $location = new Location();
        $location->setName('Old Parana');
        $location->setStreet('Tedgst');
        $manager->persist($location);
        $locations[] = $location;

        $location = new Location();
        $location->setName('Berlin');
        $location->setStreet('Shiutsfestrasse');
        $manager->persist($location);
        $locations[] = $location;

        $location = new Location();
        $location->setName('Tokio');
        $location->setStreet('Dsggssdgg ');
        $manager->persist($location);
        $locations[] = $location;

        $numberOfLocations = count($locations);


        foreach ($this->colorNames as $colorNr => $colorName) {
            $locNr = $colorNr % $numberOfLocations;
            $location = $locations[$locNr];
            $organization = new Organization();
            $organization->setName($colorName);
            $organization->setLocation($location);
            $organization->setScanPlan($scanPlans[$locNr % 4+1]);
            $manager->persist($organization);
        }

        $manager->flush();
    }
}
