<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\User;
use App\Entity\Location;
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

        $users = [];
        $user = new User();
        $email = $_ENV['ADMIN_EMAIL_INITIAL'];
        $user->setEmail($email);
        $user->setUsername('Admin');
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
            $user->setUsername($username);
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
            $organization->setScan((bool)$locNr % 2);
            $manager->persist($organization);
        }

        $manager->flush();
    }
}
